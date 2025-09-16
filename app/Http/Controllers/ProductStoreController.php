<?php

namespace App\Http\Controllers;

use App\Models\Product_store;
use Illuminate\Http\Request;

//librerias
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use App\Models\Usd_exchange_rate;
use Illuminate\Support\Facades\DB;

//Modelos
use App\Models\Product;

class ProductStoreController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productinStore = Product_store::with("product")->get();
        $usd_exchange_rate = Usd_exchange_rate::find(1);
        $productStores = $productinStore->map(function ($productstore )use($usd_exchange_rate) {
            
            $wholesale = '$us '. $productstore->unit_price_wholesale . ' ' . 'Bs. '. number_format(($usd_exchange_rate->exchange_rate * $productstore->unit_price_wholesale),2 );  
            $retail= '$us '. $productstore->unit_price_retail . ' ' . 'Bs. ' . number_format( ($usd_exchange_rate->exchange_rate * $productstore->unit_price_retail),2) ;
            $sale= '$us '.$productstore->saleprice . ' ' . 'Bs. ' . number_format(($usd_exchange_rate->exchange_rate * $productstore->saleprice),2) ;
            return [
                "id"=> $productstore->id,
                "product_id"=> $productstore->product->name,
                "quantity"=> $productstore->quantity,
                "unit_price_wholesale"=> $wholesale,
                "unit_price_retail"=> $retail,
                "saleprice"=> $sale
            ];
        }); 
        $products = Product::all();
        return Inertia::render("ProductsStore/Index", [
            "productstores"=> $productStores,
            "products"=> $products
        ]);


    }
    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::all();
        return Inertia::render("ProductsStore/create", [
            "products"=> $products
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
    {
        // 1. Validar la solicitud para el array de ítems
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price_wholesale' => 'required|numeric|min:0',
            'items.*.unit_price_retail' => 'required|numeric|min:0',
            'items.*.saleprice' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Iniciar una transacción de base de datos para garantizar la integridad
        DB::beginTransaction();

        try {
            // 2. Iterar sobre cada ítem para procesarlo individualmente
            foreach ($request->input('items') as $index => $itemData) {

                // Buscar el producto en la bodega por su ID
                $productInStock = Product::findOrFail($itemData['product_id']);

                // 3. Validar si la cantidad solicitada es mayor que el stock disponible
                if ($itemData['quantity'] > $productInStock->quantity_in_stock) {
                    // Si falla, revertimos la transacción
                    DB::rollBack();
                    // Usamos un índice dinámico para mostrar el error correctamente en el formulario
                    return redirect()->back()->withErrors(["items.{$index}.quantity" => 'La cantidad solicitada es mayor que la cantidad disponible en bodega.'])->withInput();
                }

                // 4. Buscar o crear el producto en la tabla de la tienda
                $productInStore = Product_store::firstOrNew(['product_id' => $itemData['product_id']]);

                // Actualizar la cantidad y precios
                $productInStore->quantity += $itemData['quantity'];
                $productInStore->unit_price_wholesale = $itemData['unit_price_wholesale'];
                $productInStore->unit_price_retail = $itemData['unit_price_retail'];
                $productInStore->saleprice = $itemData['saleprice'];
                $productInStore->save();

                // 5. Restar la cantidad del stock en la bodega
                $productInStock->quantity_in_stock -= $itemData['quantity'];
                $productInStock->save();
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
        $productStore->unit_price_wholesale = $request->unit_price_wholesale;
        $productStore->unit_price_retail = $request->unit_price_retail;
        $productStore->saleprice = $request->saleprice;

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
