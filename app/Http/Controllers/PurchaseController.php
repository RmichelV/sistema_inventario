<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;

//models
use App\Models\Product;

//librerias
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\Warehouse\PurchaseRequest;

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
    public function store(PurchaseRequest $request)
    {
          
        DB::beginTransaction();

        try {
            // Itera sobre el array de ítems de compra que viene del formulario
            foreach ($request->input('items') as $itemData) {
                
                // 1. Crear el registro de la compra
                Purchase::create([
                    "product_id"      => $itemData['product_id'],
                    "purchase_quantity" => $itemData['purchase_quantity'],
                    "purchase_date"   => $request->input('purchase_date'), // La fecha es la misma para todos los ítems
                ]);

                // 2. Buscar el producto para actualizar su stock
                $product = Product::find($itemData['product_id']);

                // 3. Actualizar la cantidad en stock
                if ($product) {
                    $product->quantity_in_stock += $itemData['purchase_quantity'];
                    $product->save();
                }
            }

            // Si todas las operaciones fueron exitosas, confirma la transacción
            DB::commit();

            return redirect()->route('rpurchases.index')->with('success', 'Compra registrada y stock actualizado.');

        } catch (\Exception $e) {
            // Si algo falla, revierte todos los cambios de la base de datos
            DB::rollBack();

            return redirect()->back()->with('error', 'Hubo un error al registrar la compra. Por favor, inténtalo de nuevo.');
        }

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
