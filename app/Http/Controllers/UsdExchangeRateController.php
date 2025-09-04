<?php

namespace App\Http\Controllers;

use App\Models\Usd_exchange_rate;
use Illuminate\Http\Request;

//modelos

//librerias
use Inertia\Inertia;

class UsdExchangeRateController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $usd_exchange_rates= Usd_exchange_rate::find(1);
        return Inertia::render("/Dashboard",[
            "usd"=>$usd_exchange_rates
        ]);
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
    public function show(Usd_exchange_rate $usd_exchange_rate)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Usd_exchange_rate $usd_exchange_rate)
    {
        $usd_exchange_rates= Usd_exchange_rate::find(1);
        return Inertia::render("UsdExchangeRate/edit",[
            "usd"=>$usd_exchange_rate
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $usd_exchange_rate= Usd_exchange_rate::find($id);
        $usd_exchange_rate->exchange_rate = $request->exchange_rate;
        $usd_exchange_rate->save();
        return back()->with("success","");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Usd_exchange_rate $usd_exchange_rate)
    {
        //
    }
}
