<?php

namespace App\Http\Controllers;

use App\Models\Product_store;
use Illuminate\Http\Request;

//librerias
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use App\Models\Usd_exchange_rate;

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
        // 1. Validar la solicitud
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|numeric|min:1',
            'unit_price_wholesale' => 'required|numeric|min:0',
            'unit_price_retail' => 'required|numeric|min:0',
            'saleprice' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // 2. Buscar el producto de la bodega (tabla 'products') para obtener la cantidad en stock
        $productInStock = Product::findOrFail($request->product_id);

        // 3. Validar si la cantidad solicitada es menor o igual a la cantidad en stock de bodega
        if ($request->quantity > $productInStock->quantity_in_stock) {
            return redirect()->back()->withErrors(['quantity' => 'La cantidad solicitada es mayor que la cantidad disponible en bodega.'])->withInput();
        }

        // 4. Buscar el producto en la tabla de la tienda (`product_stores`)
        $productInStore = Product_store::where('product_id', $request->product_id)->first();

        if ($productInStore) {
            // El producto ya existe en la tienda, actualizamos la cantidad
            $productInStore->quantity += $request->quantity;
            $productInStore->unit_price_wholesale = $request->unit_price_wholesale;
            $productInStore->unit_price_retail = $request->unit_price_retail;
            $productInStore->saleprice = $request->saleprice;
            $productInStore->save();
        } else {
            // El producto no existe en la tienda, creamos un nuevo registro
            Product_store::create([
                "product_id" => $request->product_id,
                "quantity" => $request->quantity,
                "unit_price_wholesale" => $request->unit_price_wholesale,
                "unit_price_retail" => $request->unit_price_retail,
                "saleprice" => $request->saleprice,
            ]);
        }
        
        // 5. Restar la cantidad del stock en la bodega
        $productInStock->quantity_in_stock -= $request->quantity;
        $productInStock->save();

        return redirect()->route('rproductstores.index')->with('success', 'Producto transferido a la tienda exitosamente.');
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
    public function edit(Product_store $product_store)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product_store $product_store)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product_store $product_store)
    {
        //
    }
}
