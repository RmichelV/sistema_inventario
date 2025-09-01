<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;

//models
use App\Models\Product;

//librerias
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;


class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
   public function index()
    {
        // 1. Ejecuta la consulta para obtener la colección de productos
        $products = Product::all();

        // 2. Llama a la relación con el nombre correcto y en minúscula
        $purchasesList = Purchase::with("product")->get();
        // dd($purchasesList);
        $purchases = $purchasesList->map(function ($purchase) {
            return [
                "id" => $purchase->id,
                "product" => $purchase->product->code,
                "purchase_quantity" => $purchase->purchase_quantity,
                "purchase_date" => $purchase->purchase_date, 
            ];
        });
   

        return Inertia::render("Purchases/Index", [
            "products" => $products,
            "purchases" => $purchases
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::all();
        $purchases = Purchase::all();
        return Inertia::render("Purchases/create", compact("products","purchases"));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
          
        $purchase = Purchase::create([
            "product_id"=> $request->product_id,
            "purchase_quantity"=> $request->purchase_quantity,
            "purchase_date"=> $request->purchase_date,
        ]);

        $product = Product::find($request->product_id);

        // 4. Actualiza la cantidad en stock
        if ($product) {
            $product->quantity_in_stock += $request->purchase_quantity;
            $product->save();
        }

        return redirect()->route('rpurchases.index')->with('success', 'Compra registrada y stock actualizado.');

    }

    /**
     * Display the specified resource.
     */
    public function show(Purchase $purchase)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Purchase $purchase)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Purchase $purchase)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase)
    {
        //
    }
}
