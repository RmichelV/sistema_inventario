<?php

namespace App\Http\Controllers;

use App\Models\Reservation_item;
use App\Models\Reservation;
use App\Models\product_branch as ProductBranch;
use App\Models\Product_store;
use Illuminate\Http\Request;

class ReservationItemController extends Controller
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
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(Reservation_item $reservation_item)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reservation_item $reservation_item)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reservation_item $reservation_item)
    {
        //
    }

    /**
     * Remove the specified resource from storage and revert stock.
     */
    public function destroy($id)
    {
        try {
            $item = Reservation_item::findOrFail($id);
            $reservation = $item->reservation;
            
            // Obtener la sucursal del usuario autenticado
            $authUser = auth()->user();
            $branchId = $authUser->branch_id ?? null;

            // Obtener información del producto y cantidad
            $quantityWarehouse = floatval($item->quantity_from_warehouse ?? 0);
            $quantityStore = floatval($item->quantity_from_store ?? 0);
            $totalQuantity = $quantityWarehouse + $quantityStore;
            $productId = $item->product_id;
            $itemPrice = floatval($item->total_price);

            // Devolver TODO el stock a bodega (product_branches)
            $currentDate = now()->toDateString();
            if ($totalQuantity > 0) {
                $productInBranch = ProductBranch::where('branch_id', $branchId)
                    ->where('product_id', $productId)
                    ->first();

                if ($productInBranch) {
                    $productInBranch->increment('quantity_in_stock', $totalQuantity);
                    $productInBranch->update(['last_update' => $currentDate]);
                }
            }

            // Actualizar los montos de la reservación
            $reservation->total_amount -= $itemPrice;
            
            // Si el anticipo es mayor al nuevo total, ajustar el anticipo
            if ($reservation->advance_amount > $reservation->total_amount) {
                $reservation->advance_amount = $reservation->total_amount;
            }
            
            $reservation->rest_amount = $reservation->total_amount - $reservation->advance_amount;
            $reservation->save();

            // Eliminar el item
            $item->delete();

            return back()->with('success', 'Producto eliminado y stock devuelto a bodega.');

        } catch (\Exception $e) {
            \Log::error('Error al eliminar item de reservación: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al eliminar el producto.');
        }
    }
}