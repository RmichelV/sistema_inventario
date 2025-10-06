<?php

namespace App\Http\Controllers;

use App\Models\Devolution;
use Illuminate\Http\Request;

//librerias
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
//modelos
use App\Models\Product;
use App\Models\Sale;
use App\Models\Sale_item;
use App\Models\Product_store;

//Validaciones 
use App\Http\Requests\Devolution\DevolutionRequest;


class DevolutionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DevolutionRequest $request)
    {

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Find the original sale item that's being returned
            $saleItem = Sale_item::find($request->sale_item_id); 

            // If the sale item doesn't exist, throw an exception to roll back the transaction
            if (!$saleItem) {
                throw new \Exception('El ítem de venta no fue encontrado.');
            }

            $quantity_sold = $saleItem->quantity_products;
            $quantity_to_return = $request->quantity;
            
            // Calculate the new quantity of products on the sale item
            $new_quantity_sold = $quantity_sold - $quantity_to_return;

            // Find the product in the product_stores table to update its stock
            $productStore = Product_store::where('product_id', $request->product_id)->first();
            
            if ($productStore) {
                // Update the quantity in stock by adding the returned quantity
                $productStore->quantity += $quantity_to_return;
                $productStore->save();
            }

            // Update or delete the sale item based on the returned quantity
            if ($new_quantity_sold == 0) {
                $saleItem->delete();

            } else {
                $saleItem->quantity_products = $new_quantity_sold;
                // Recalculate the new total_price for the updated sale item
                $unitPrice = $saleItem->total_price / $quantity_sold;
                $saleItem->total_price = $unitPrice * $new_quantity_sold;
                $saleItem->save();
            }
        

            // Create the new devolution record
            Devolution::create([
                'product_id' => $request->product_id,
                'quantity' => $request->quantity,
                'reason' => $request->reason,
                'refund_amount' => $request->refund_amount,
            ]);

            // If everything was successful, commit the transaction
            DB::commit();

            return redirect()->route('rproductstores.index')->with('success', 'Devolución registrada con éxito.');

        } catch (\Exception $e) {
            // If an error occurs, roll back all database changes
            DB::rollBack();

            return redirect()->back()->with('error', 'Hubo un error al registrar la devolución. Por favor, inténtalo de nuevo.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Devolution $devolution)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Devolution $devolution)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Devolution $devolution)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Devolution $devolution)
    {
        //
    }
}
