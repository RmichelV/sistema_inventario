<?php

namespace App\Http\Controllers;

use App\Models\Reservation_item;
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
     * Remove the specified resource from storage.
     */
    public function destroy(Reservation_item $reservation_item)
    {
        try {
            $reservation_item->delete();
            return redirect()->back()->with('success', 'Producto eliminado de la reservación.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar el producto.');
        }
    }
}
