<?php

namespace App\Http\Controllers;

use App\Models\Sale_item;
use Illuminate\Http\Request;

//librerias
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Requests\Store\SaleRequest;

//modelos
use App\Models\Product;
use App\Models\Product_store;
use App\Models\Usd_exchange_rate;
use App\Models\Sale;


class SaleItemController extends Controller
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
    public function show(Sale_item $sale_item)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $sale_item = Sale_item::find($id);
        $product = Product::find($sale_item->product_id);
        $sale = Sale::all();

        return Inertia::render('Sales/edit',[
            'saleItem'=> $sale_item,
            'product'=> $product,
            'sale' => $sale
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale_item $sale_item)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale_item $sale_item)
    {
        //
    }
}
