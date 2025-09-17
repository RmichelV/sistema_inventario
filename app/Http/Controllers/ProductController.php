<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

//mis librerias 
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

//validaciones
use App\Http\Requests\Warehouse\ProductRequest;

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
    public function store(ProductRequest $request)
    {
        DB::beginTransaction();

        try {
            // Itera sobre el array de productos que viene en la petición.
            // Usamos el $index para acceder a los archivos subidos.
            foreach ($request->input('products') as $index => $productData) {
                
                // 1. Manejar la imagen de cada producto de forma correcta.
                $img_name = null;
                // Usamos hasFile() con la notación de punto para verificar si el archivo existe.
                if ($request->hasFile("products.{$index}.img_product")) {
                    // Accedemos al objeto UploadedFile usando la notación de punto.
                    $imgProduct = $request->file("products.{$index}.img_product");
                    
                    // Asegurarse de que $imgProduct es un objeto UploadedFile válido
                    if ($imgProduct && $imgProduct->isValid()) {
                        $extension = $imgProduct->getClientOriginalExtension();
                        $img_name = $productData['code'] . '-' . time() . '.' . $extension;
                        
                        // Guardar la imagen en el disco público.
                        $imgProduct->storeAs('/product_images', $img_name,);
                    }
                }

                // 2. Crear un nuevo registro de producto en la base de datos.
                Product::create([
                    'name' => $productData['name'],
                    'code' => $productData['code'],
                    'img_product' => $img_name,
                    'quantity_in_stock' => $productData['quantity_in_stock'],
                    'units_per_box' => $productData['units_per_box'],
                   
                ]);
            }

            // Si todas las inserciones fueron exitosas, confirma la transacción.
            DB::commit();

            return redirect()->route('rproducts.index')->with('success', 'Productos registrados exitosamente.');

        } catch (\Exception $e) {
            // Si algo falla, revierte todos los cambios.
            DB::rollBack();

            return redirect()->back()->with('error', 'Hubo un error al registrar los productos.');
        }
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
