<?php

namespace App\Http\Controllers;

use App\Models\Product_store;
use Illuminate\Http\Request;

//librerias
use Inertia\Inertia;
use App\Models\Usd_exchange_rate;
use Illuminate\Support\Facades\DB;

//Modelos
use App\Models\Product;
use App\Models\product_branch;
use Illuminate\Support\Facades\Auth;

//Requests
use App\Http\Requests\Store\ProductStoreRequest;

class ProductStoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $branchId = Auth::user()->branch_id ?? null;

        // Si la columna branch_id existe en la tabla product_stores, filtramos por ella;
        // si no existe (antes de migrar), devolvemos todos los registros para compatibilidad.
        if (\Schema::hasColumn('product_stores', 'branch_id') && $branchId) {
            $productinStore = Product_store::with("product")->where('branch_id', $branchId)->get();
        } else {
            $productinStore = Product_store::with("product")->get();
        }
        $usd_exchange_rate = Usd_exchange_rate::find(1);
        $productStores = $productinStore->map(function ($productstore )use($usd_exchange_rate) {
            // unit_price en la tabla ya representa el precio final por unidad (dólares)
            $storedPrice = $productstore->unit_price ?? 0;

            $unit_price_bs = 'Bs. ' . number_format(($usd_exchange_rate->exchange_rate * $storedPrice),2);
            $porcentaje = 'Bs. ' . number_format((($usd_exchange_rate->exchange_rate * $storedPrice)*1.1),2);

            return [
                "id"=> $productstore->id,
                "product_id"=> $productstore->product->code,
                "quantity"=> $productstore->quantity,
                // unit_price aquí representará el precio FINAL por unidad (dólares)
                "unit_price"=> round($storedPrice, 2),
                "unit_price_bs"=>$unit_price_bs,
                "porcentaje"=>$porcentaje,
                "price_multiplier" => $productstore->price_multiplier ?? 1.0,
            ];
        }); 
        // Obtener solo productos que están en la bodega de la sucursal (product_branches)
        $productIds = product_branch::where('branch_id', $branchId)->pluck('product_id')->toArray();
        $products = Product::whereIn('id', $productIds)->get();

        // Sucursales y usuario actual para el selector en frontend
        $user = Auth::user();
        $branches = \App\Models\branch::all();
        $currentBranch = null;
        if ($user && $user->branch_id) {
            $currentBranch = $branches->firstWhere('id', $user->branch_id);
        }

        return Inertia::render("ProductsStore/Index", [
            "productstores"=> $productStores,
            "products"=> $products,
            'branches' => $branches,
            'currentBranch' => $currentBranch,
            'currentUser' => $user,
        ]);


    }
    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $branchId = Auth::user()->branch_id ?? null;
        if (\Schema::hasColumn('product_branches', 'branch_id') && $branchId) {
            // Obtener product_branches para la sucursal y devolver los productos enriquecidos
            $productBranches = product_branch::with('product')
                ->where('branch_id', $branchId)
                ->get();

            $products = $productBranches->map(function ($pb) {
                $product = $pb->product;

                return [
                    'id' => $product->id,
                    'name' => $product->name ?? null,
                    'code' => $product->code ?? null,
                    'img' => $product->img ?? null,
                    'quantity_in_stock' => $pb->quantity_in_stock ?? 0,
                    'unit_price' => $pb->unit_price ?? 0,
                    'units_per_box' => $pb->units_per_box ?? null,
                ];
            })->values();
        } else {
            $products = Product::all();
        }

        $user = Auth::user();
        $branches = \App\Models\branch::all();
        $currentBranch = null;
        if ($user && $user->branch_id) {
            $currentBranch = $branches->firstWhere('id', $user->branch_id);
        }

        return Inertia::render("ProductsStore/create", [
            "products"=> $products,
            'branches' => $branches,
            'currentBranch' => $currentBranch,
            'currentUser' => $user,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(ProductStoreRequest $request)
    {
        // Iniciar una transacción de base de datos para garantizar la integridad
        DB::beginTransaction();

        try {
            // 2. Iterar sobre cada ítem para procesarlo individualmente
            $branchId = Auth::user()->branch_id ?? null;

            foreach ($request->input('items') as $index => $itemData) {

                // Buscar el stock en product_branches para la sucursal
                $productInBranch = product_branch::where('branch_id', $branchId)
                    ->where('product_id', $itemData['product_id'])
                    ->first();

                if (!$productInBranch || ($itemData['quantity'] > ($productInBranch->quantity_in_stock ?? 0))) {
                    DB::rollBack();
                    return redirect()->back()->withErrors(["items.{$index}.quantity" => 'La cantidad solicitada es mayor que la cantidad disponible en la bodega de esta sucursal.'])->withInput();
                }

                // Buscar o crear el producto en la tabla de la tienda para esta sucursal
                $productInStore = Product_store::firstOrNew([
                    'product_id' => $itemData['product_id'],
                    'branch_id' => $branchId,
                ]);

                // Actualizar la cantidad
                $productInStore->quantity = ($productInStore->quantity ?? 0) + $itemData['quantity'];

                // Determinar el unit_price efectivo: si viene en el request, usarlo; si no, calcular desde product_branches.unit_price * price_multiplier
                $effectiveUnitPrice = null;
                if (isset($itemData['unit_price']) && $itemData['unit_price'] !== null) {
                    $effectiveUnitPrice = $itemData['unit_price'];
                } else {
                    $base = $productInBranch->unit_price ?? 0;
                    // price_multiplier ahora es un factor multiplicativo (ej. 1.1 para +10%)
                    $mult = isset($itemData['price_multiplier']) ? $itemData['price_multiplier'] : ($productInStore->price_multiplier ?? 1.0);
                    $effectiveUnitPrice = round($base * $mult, 2);
                }
                $productInStore->unit_price = $effectiveUnitPrice;
                // Guardar multiplicador si viene (ej. 1.1)
                if (isset($itemData['price_multiplier'])) {
                    $productInStore->price_multiplier = $itemData['price_multiplier'];
                }
                $productInStore->last_update = now()->toDateString();
                $productInStore->branch_id = $branchId;
                $productInStore->save();

                // Restar la cantidad del stock en la bodega (product_branches)
                $productInBranch->quantity_in_stock = ($productInBranch->quantity_in_stock ?? 0) - $itemData['quantity'];
                $productInBranch->save();
            }

            // Si todas las operaciones fueron exitosas, confirma la transacción
            DB::commit();

            return redirect()->route('rproductstores.index')->with('success', 'Productos transferidos a la tienda exitosamente.');

        } catch (\Exception $e) {
            // Si algo falla, revierte todos los cambios de la base de datos
            DB::rollBack();

            return redirect()->back()->with('error', 'Ocurrió un error inesperado al transferir los productos. Inténtalo de nuevo.')->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Product_store $product_store)
    {
        //
    }
    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $productStore = Product_store::findOrFail($id);
        $products = Product::all();

        return Inertia::render('ProductsStore/edit',
        [
            'productStore' => $productStore, // Pasa el modelo original
            'products' => $products
        ]);
    }

    public function update(Request $request, string $id)
    {
        $productStore = Product_store::findOrFail($id);
        $productStore->product_id = $request->product_id;
        $productStore->quantity = $request->quantity;
        $productStore->unit_price = $request->unit_price;
        if ($request->has('price_multiplier')) {
            $productStore->price_multiplier = $request->price_multiplier;
        }

        $productStore->save();
        return redirect()->route('rproductstores.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product_store $product_store)
    {
        //
    }
}
