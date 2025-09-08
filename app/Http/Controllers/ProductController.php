<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Mockery\Undefined;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $productsList = Product::all();

        $products = $productsList->map(function ($product) {
         $productsQuantity = $product->quantity_in_stock;
        $productBoxes = $product->units_per_box;

        // cuántas cajas completas caben
        $fullBoxes = intdiv($productsQuantity, $productBoxes);

        // cuántas unidades sobran
        $remainder = $productsQuantity % $productBoxes;

        if ($remainder === 0) {
            // todas las cajas están cerradas
            $productBoxesQuantityT = $fullBoxes;
        } else {
            // hay cajas completas + 1 abierta
            if ($fullBoxes > 0) {
                $productBoxesQuantityT = $fullBoxes . ' Y 1 abierta';
            } else {
                $productBoxesQuantityT = '0 pero 1 abierta';
            }
        }
            // else{
            //     if($productBoxesQuantity < 1) {
            //         $productBoxesQuantityT = '0 pero 1 caja abierta'; 
            //     }
            //     else{
            //         $productBoxesQuantityT = 'Cantidad de cajas: ' . $productBoxesQuantity .' pero una caja abierta'; 
            //     }
            // }
            $Imgname = $product->img_product;
            $routeImg = asset('/storage/product_images/' . $Imgname);
            return [
                "id"=> $product->id,
                "name"=> $product->name,
                "code" => $product->code,
                "img_product" => $routeImg,
                "quantity_in_stock" => $product->quantity_in_stock,
                "boxes" => $productBoxesQuantityT,
                "units_per_box" => $product->units_per_box,
            ];
        });
        return Inertia::render("Products/Index", [
            "products"=> $products]);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $products = Product::all();
        return Inertia::render("Products/create",[
            "products"=> $products
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
      
        $img_name=null;
        if ($request->hasFile('img_product')) {
            $imgProduct = $request->file('img_product');
            $extension = $imgProduct->getClientOriginalExtension();
            $img_name = $request->input('code') . '-' . time() . '.' . $extension;
            
            // 3. Guardar la imagen en el disco público de Laravel
            $imgProduct->storeAs('/product_images', $img_name,);
        }

        // $datos = $request->all();
        // dd($datos);

        
        $product = Product::create([
            'name' => $request->name,
            'code' => $request->code,
            'img_product' => $img_name,
            'quantity_in_stock' => $request->quantity_in_stock,
            'units_per_box' => $request->units_per_box,
            'minimum_wholesale_quantity' => $request->minimum_wholesale_quantity,
            'currency_type' => $request->currency_type,
            'unit_price_wholesale' => $request->unit_price_wholesale,
            'unit_price_retail' => $request->unit_price_retail,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Product $product)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Product $product)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Product $product)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        //
    }
}
