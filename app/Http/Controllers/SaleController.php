<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

//librerias
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Requests\Store\SaleRequest;

//modelos
use App\Models\Product;
use App\Models\Product_store;
use App\Models\product_branch as ProductBranch;
use App\Models\Usd_exchange_rate;
use App\Models\Sale_item;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $authUser = auth()->user();
        $branchId = $authUser->branch_id ?? null;

        if ($branchId) {
            $sales = Sale::where('branch_id', $branchId)->latest()->get();
        } else {
            $sales = collect([]);
        }

        $branches = \App\Models\branch::all();
        $currentBranch = null;
        if ($authUser && $authUser->branch_id) {
            $currentBranch = $branches->firstWhere('id', $authUser->branch_id);
        }

        return Inertia::render('Sales/Index',[
            'sales'=>$sales,
            'branches' => $branches,
            'currentBranch' => $currentBranch,
            'currentUser' => $authUser,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {   
        $authUser = auth()->user();
        $branchId = $authUser->branch_id ?? null;

        $branches = \App\Models\branch::all();
        $currentBranch = null;
        if ($branchId) {
            $currentBranch = $branches->firstWhere('id', $branchId);
        }

        // Si no hay branch asignado, devolver colecciones vacías
        if (!$branchId) {
            return Inertia::render('Sales/create',[
                'products' => collect([]),
                'productStores' => collect([]),
                'usd_exchange_rate' => Usd_exchange_rate::find(1),
                'branches' => $branches,
                'currentBranch' => $currentBranch,
                'currentUser' => $authUser,
            ]);
        }

        // Obtener productos en bodega para la sucursal (product_branches)
        $productBranches = ProductBranch::with('product')
            ->where('branch_id', $branchId)
            ->get();

        $products = $productBranches->map(function ($pb) {
            $product = $pb->product;
            $Imgname = $product->img_product;
            $routeImg = $Imgname ? '/storage/product_images/' . $Imgname : null;
            return [
                'id' => $product->id,
                'name' => $product->name,
                'code' => $product->code,
                'img_product' => $routeImg,
                'quantity_in_stock' => $pb->quantity_in_stock,
                'unit_price' => $pb->unit_price ?? 0,
                'units_per_box' => $pb->units_per_box,
                'last_update' => $pb->last_update,
            ];
        });

        // Obtener product_stores solo de la sucursal
        $productStores = Product_store::where('branch_id', $branchId)->get();

        return Inertia::render('Sales/create',[
            'products'=>$products,
            'productBranches' => $productBranches,
            'productStores' => $productStores,
            'usd_exchange_rate'=>Usd_exchange_rate::find(1),
            'branches' => $branches,
            'currentBranch' => $currentBranch,
            'currentUser' => $authUser,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SaleRequest $request)
    {
       
        
        $exchange_rate = Usd_exchange_rate::find(1);
        DB::beginTransaction();

        try {
            // 3. Create the sale first and attach branch_id from authenticated user
            $authUser = auth()->user();
            $sale = Sale::create([
                'sale_code' => 'EWTTO-' . Str::random(8),
                'customer_name' => $request->customer_name,
                'sale_date' => $request->sale_date,
                'pay_type' => $request->pay_type,
                'final_price' => $request->final_price,
                'exchange_rate' => $exchange_rate->exchange_rate,
                'branch_id' => $authUser->branch_id ?? null,
            ]);

            // 4. Process each item in the request
            $branchId = $authUser->branch_id ?? null;
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);

                // Obtener el stock en bodega por sucursal (product_branches)
                $productInBranch = ProductBranch::where('branch_id', $branchId)
                    ->where('product_id', $product->id)
                    ->first();

                // Obtener el stock en tienda para la sucursal
                $productInStore = Product_store::where('product_id', $product->id)
                    ->where('branch_id', $branchId)
                    ->first();

                $quantityFromWarehouse = $item['quantity_from_warehouse'] ?? 0;
                $quantityFromStore = $item['quantity_from_store'] ?? 0;
                $totalQuantity = $quantityFromWarehouse + $quantityFromStore;

                // 5. Validate available stock for each item using product_branches for warehouse
                if ($quantityFromWarehouse > 0) {
                    if (!$productInBranch || $quantityFromWarehouse > ($productInBranch->quantity_in_stock ?? 0)) {
                        DB::rollBack();
                        return redirect()->back()->with('error', "La cantidad de {$product->name} solicitada de bodega excede el stock disponible.")->withInput();
                    }
                }

                if ($productInStore && $quantityFromStore > $productInStore->quantity) {
                    DB::rollBack();
                    return redirect()->back()->with('error', "La cantidad de {$product->name} solicitada de tienda excede el stock disponible.")->withInput();
                }

                // 6. Create the sale item
                Sale_item::create([
                    'sale_id' => $sale->id,
                    'product_id' => $product->id,
                    'quantity_products' => $totalQuantity,
                    'total_price' => $item['selected_price'],
                    'exchange_rate' => $exchange_rate->exchange_rate
                ]);

                // 7. Update stock
                $currentDate = now()->toDateString(); // Obtener la fecha en formato DATE

                // Actualización para productos vendidos desde Bodega (product_branches)
                if ($quantityFromWarehouse > 0 && $productInBranch) {
                    $productInBranch->decrement('quantity_in_stock', $quantityFromWarehouse);
                    $productInBranch->update(['last_update' => $currentDate]);
                }

                // Actualización para productos vendidos desde Tienda (product_stores)
                if ($quantityFromStore > 0 && $productInStore) {
                    $productInStore->decrement('quantity', $quantityFromStore);
                    // Actualizar el campo 'last_update' en el modelo Product_store
                    $productInStore->update(['last_update' => $currentDate]);
                }
            }

            // 8. Commit the transaction
            DB::commit();

            return redirect()->route('rsales.index')->with('success', 'Venta registrada exitosamente.');

        } catch (\Exception $e) {
            // Revert all changes if an error occurs
            DB::rollBack();

            return redirect()->back()->with('error', 'Ocurrió un error al registrar la venta. Por favor, inténtelo de nuevo.')->withInput();
        }
    }
    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $sale = Sale::find($id);
        $sale_items = Sale_item::where('sale_id', $sale->id)->get();
        $product_ids = $sale_items->pluck('product_id')->toArray();
        $products = Product::whereIn('id', $product_ids)->get();
        $authUser = auth()->user();

        return Inertia::render('Sales/show', [
            'sale' => $sale,
            'sale_items' => $sale_items,
            'products' => $products,
            'currentUser' => $authUser,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        
    }   

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        //
    }
}
