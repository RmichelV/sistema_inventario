<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\product_branch as ProductBranch;
use Illuminate\Http\Request;

//mis librerias 
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;

//validaciones
use App\Http\Requests\Warehouse\ProductRequest;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Obtener branch_id del usuario autenticado
        $user = auth()->user();
        $branchId = $user->branch_id ?? null;

        // Obtener todas las sucursales para el selector (solo se mostrará a admin en frontend)
        $branches = \App\Models\branch::all();
        $currentBranch = null;
        if ($branchId) {
            $currentBranch = $branches->firstWhere('id', $branchId);
        }

        // Si no hay branch asociado, devolvemos vacío
        if (!$branchId) {
            return Inertia::render('Products/Index', ['products' => collect([])]);
        }

        // Obtenemos los registros de inventario para la sucursal y cargamos el producto relacionado
        $productBranches = ProductBranch::with('product')
            ->where('branch_id', $branchId)
            ->get();

        $products = $productBranches->map(function ($pb) {
            $productsQuantity = $pb->quantity_in_stock ?? 0;
            $productBoxes = $pb->units_per_box ?? 0;

            // cálculo seguro de cajas
            $productBoxesQuantityT = 'N/A';
            if ($productBoxes > 0) {
                $fullBoxes = intdiv($productsQuantity, $productBoxes);
                $remainder = $productsQuantity % $productBoxes;

                if ($remainder === 0) {
                    $productBoxesQuantityT = $fullBoxes;
                } else {
                    if ($fullBoxes > 0) {
                        $productBoxesQuantityT = $fullBoxes . ' Y 1 abierta';
                    } else {
                        $productBoxesQuantityT = '0 pero 1 abierta';
                    }
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
            $product = $pb->product;
            $Imgname = $product->img_product;
            // Usar ruta relativa dentro de public/storage para evitar depender de APP_URL
            $routeImg = $Imgname ? '/storage/product_images/' . $Imgname : null;
            return [
                "id"=> $product->id,
                "name"=> $product->name,
                "code" => $product->code,
                "img_product" => $routeImg,
                "quantity_in_stock" => $pb->quantity_in_stock,
                "unit_price" => $pb->unit_price ?? 0,
                "boxes" => $productBoxesQuantityT,
                "units_per_box" => $pb->units_per_box,
                "last_update" => $pb->last_update,
            ];
        });
        return Inertia::render("Products/Index", [
            "products"=> $products,
            'branches' => $branches,
            'currentBranch' => $currentBranch,
            'currentUser' => $user,
        ]);

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
                        
                        // Guardar la imagen en el disco público (storage/app/public/product_images)
                        $imgProduct->storeAs('product_images', $img_name, 'public');
                    }
                }

                // 2. Reutilizar producto existente si hay uno con el mismo código, o crear nuevo.
                $product = Product::where('code', $productData['code'])->first();

                if (!$product) {
                    $product = Product::create([
                        'name' => $productData['name'] ?? null,
                        'code' => $productData['code'],
                        'img_product' => $img_name,
                    ]);
                } else {
                    // Si existe y se subió una nueva imagen, actualízala
                    $updateData = [];
                    if (!empty($productData['name'])) {
                        $updateData['name'] = $productData['name'];
                    }
                    if ($img_name) {
                        $updateData['img_product'] = $img_name;
                    }
                    if (!empty($updateData)) {
                        $product->update($updateData);
                    }
                }

                // 3. Crear o actualizar el registro de inventario para la sucursal actual en 'product_branches'
                $user = auth()->user();
                $branchId = $user->branch_id ?? null;

                if ($branchId) {
                    $pb = ProductBranch::where('branch_id', $branchId)
                        ->where('product_id', $product->id)
                        ->first();

                    if ($pb) {
                        // Sumar la cantidad ingresada al stock existente
                        $pb->quantity_in_stock = ($pb->quantity_in_stock ?? 0) + (int)($productData['quantity_in_stock'] ?? 0);
                        // Actualizar unidades por caja si viene
                        if (isset($productData['units_per_box'])) {
                            $pb->units_per_box = $productData['units_per_box'];
                        }
                        // Actualizar unit_price si viene en el request
                        if (isset($productData['unit_price'])) {
                            $pb->unit_price = $productData['unit_price'];
                        }
                        $pb->last_update = now()->toDateString();
                        $pb->save();
                    } else {
                        try {
                            ProductBranch::create([
                                'branch_id' => $branchId,
                                'product_id' => $product->id,
                                'quantity_in_stock' => $productData['quantity_in_stock'] ?? 0,
                                'units_per_box' => $productData['units_per_box'] ?? null,
                                'unit_price' => $productData['unit_price'] ?? 0,
                                'last_update' => now()->toDateString(),
                            ]);
                        } catch (QueryException $qe) {
                            // En caso de condición de carrera donde otro proceso creó el registro,
                            // recuperamos el registro existente y sumamos la cantidad.
                            $existing = ProductBranch::where('branch_id', $branchId)
                                ->where('product_id', $product->id)
                                ->first();

                            if ($existing) {
                                $existing->quantity_in_stock = ($existing->quantity_in_stock ?? 0) + (int)($productData['quantity_in_stock'] ?? 0);
                                if (isset($productData['units_per_box'])) {
                                    $existing->units_per_box = $productData['units_per_box'];
                                }
                                $existing->last_update = now()->toDateString();
                                $existing->save();
                            } else {
                                // Si no existe por alguna razón, relanzar la excepción para que sea manejada arriba
                                throw $qe;
                            }
                        }
                    }
                }
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

    /**
     * Delete the product_branch record for the authenticated user's branch.
     * The {product} route parameter is the product id.
     */
    public function destroyBranch($productId)
    {
        $user = auth()->user();
        $branchId = $user->branch_id ?? null;

        if (!$branchId) {
            return redirect()->back()->with('error', 'Usuario sin sucursal asignada.');
        }

        $pb = ProductBranch::where('product_id', $productId)
            ->where('branch_id', $branchId)
            ->first();

        if (!$pb) {
            return redirect()->back()->with('error', 'Registro de inventario no encontrado para este producto en la sucursal.');
        }

        try {
            $pb->delete();
            return redirect()->back()->with('success', 'Registro de inventario eliminado.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar el registro de inventario.');
        }
    }
}
