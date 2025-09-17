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
use App\Http\Requests\Sales\SaleRequest;

//modelos
use App\Models\Product;
use App\Models\Product_store;
use App\Models\Usd_exchange_rate;
use App\Models\Sale_item;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sales = Sale::all();
        return Inertia::render('Sales/Index',[
            'sales'=>$sales,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {   
        $products = Product::all();
        $productStores=Product_store::all();
        $usd_exchange_rates = Usd_exchange_rate::find(1);

        return Inertia::render('Sales/create',[
            'products'=>$products,
            'productStores' => $productStores,
            'usd_exchange_rate'=>$usd_exchange_rates
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SaleRequest $request)
    {
        // 2. Start a database transaction
        DB::beginTransaction();

        try {
            // 3. Create the sale first
            $sale = Sale::create([
                'sale_code' => 'EWTTO-' . Str::random(8),
                'customer_name' => $request->customer_name,
                'sale_date' => $request->sale_date,
                'pay_type' => $request->pay_type,
                'final_price' => $request->final_price,
            ]);

            // 4. Process each item in the request
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $productInStore = Product_store::where('product_id', $item['product_id'])->first();

                $quantityFromWarehouse = $item['quantity_from_warehouse'] ?? 0;
                $quantityFromStore = $item['quantity_from_store'] ?? 0;
                $totalQuantity = $quantityFromWarehouse + $quantityFromStore;
                
                // 5. Validate available stock for each item
                if ($quantityFromWarehouse > $product->quantity_in_stock) {
                    DB::rollBack();
                    return redirect()->back()->with('error', "La cantidad de {$product->name} solicitada de bodega excede el stock disponible.")->withInput();
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
                ]);

                // 7. Update stock
                if ($quantityFromWarehouse > 0) {
                    $product->decrement('quantity_in_stock', $quantityFromWarehouse);
                }
                if ($quantityFromStore > 0 && $productInStore) {
                    $productInStore->decrement('quantity', $quantityFromStore);
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
        return Inertia::render('Sales/show', [
            'sale' => $sale,
            'sale_items' => $sale_items,
            'products' => $products,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $id)
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
