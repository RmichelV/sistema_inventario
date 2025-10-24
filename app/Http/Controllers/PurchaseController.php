<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;

//models
use App\Models\Product;
use App\Models\product_branch;
use App\Models\Product_store;
use Illuminate\Support\Facades\Auth;

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
        $branchId = Auth::user()->branch_id;

        $products = Product::all();

        // 2. Obtenemos solo las compras de la sucursal del usuario autenticado
        $purchasesList = Purchase::with("product")->where('branch_id', $branchId)->get();
        // dd($purchasesList);
        $purchases = $purchasesList->map(function ($purchase) {
            return [
                "id" => $purchase->id,
                "product" => $purchase->product->code,
                "purchase_quantity" => $purchase->purchase_quantity,
                "purchase_date" => $purchase->purchase_date, 
            ];
        });
   

        // Añadir info de sucursales y usuario actual para el selector en frontend
        $user = Auth::user();
        $branches = \App\Models\branch::all();
        $currentBranch = null;
        if ($user && $user->branch_id) {
            $currentBranch = $branches->firstWhere('id', $user->branch_id);
        }

        return Inertia::render("Purchases/Index", [
            "products" => $products,
            "purchases" => $purchases,
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
        $branchId = Auth::user()->branch_id;

        // Solo obtener productos que existan en inventario para la sucursal actual
        // y enriquecerlos con información de product_branches (precio por sucursal, stock)
        $productBranches = product_branch::with('product')
            ->where('branch_id', $branchId)
            ->get();

        $products = $productBranches->map(function ($pb) {
            $product = $pb->product;
            return [
                'id' => $product->id,
                'name' => $product->name ?? null,
                'code' => $product->code ?? null,
                'img' => $product->img ?? null,
                'quantity_in_stock' => $pb->quantity_in_stock ?? 0,
                'unit_price' => $pb->unit_price ?? 0,
                'units_per_box' => $pb->units_per_box ?? null,
            ];
        })->values();

        // Solo mostrar compras de la sucursal actual (si es necesario en la vista)
        $purchases = Purchase::where('branch_id', $branchId)->get();

        $user = Auth::user();
        $branches = \App\Models\branch::all();
        $currentBranch = null;
        if ($user && $user->branch_id) {
            $currentBranch = $branches->firstWhere('id', $user->branch_id);
        }

        return Inertia::render("Purchases/create", [
            'products' => $products,
            'purchases' => $purchases,
            'branches' => $branches,
            'currentBranch' => $currentBranch,
            'currentUser' => $user,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PurchaseRequest $request)
    {
          
        DB::beginTransaction();

        try {
            // Itera sobre el array de ítems de compra que viene del formulario
            $branchId = Auth::user()->branch_id;

            foreach ($request->input('items') as $itemData) {
                // 1. Crear el registro de la compra con branch_id
                $purchasePayload = [
                    "product_id"      => $itemData['product_id'],
                    "purchase_quantity" => $itemData['purchase_quantity'],
                    "purchase_date"   => $request->input('purchase_date'), // La fecha es la misma para todos los ítems
                    "branch_id"       => $branchId,
                ];

                if (isset($itemData['unit_price'])) {
                    $purchasePayload['unit_price'] = $itemData['unit_price'];
                }
                if (isset($itemData['total_price'])) {
                    $purchasePayload['total_price'] = $itemData['total_price'];
                }

                Purchase::create($purchasePayload);

                // 2. Actualizar o crear el registro en product_branches (inventario por sucursal)
                $productBranch = product_branch::where('branch_id', $branchId)
                    ->where('product_id', $itemData['product_id'])
                    ->first();

                if ($productBranch) {
                    $productBranch->quantity_in_stock += $itemData['purchase_quantity'];
                    // Si el formulario envía unit_price, actualizar el precio en la bodega
                    if (isset($itemData['unit_price'])) {
                        $productBranch->unit_price = $itemData['unit_price'];
                    }
                    $productBranch->last_update = now();
                    $productBranch->save();
                } else {
                    product_branch::create([
                        'branch_id' => $branchId,
                        'product_id' => $itemData['product_id'],
                        'quantity_in_stock' => $itemData['purchase_quantity'],
                        'unit_price' => $itemData['unit_price'] ?? 0,
                        'last_update' => now(),
                    ]);
                }

                // --- Actualizar o crear product_store para la sucursal, aplicando el multiplicador existente ---
                // Esto asegura que cuando registramos una compra, el precio en product_store se actualice
                // usando la lógica: precio_final_en_store = base_price_from_purchase_or_branch * price_multiplier
                try {
                    $productStore = Product_store::firstOrNew([
                        'product_id' => $itemData['product_id'],
                        'branch_id' => $branchId,
                    ]);

                    // Si viene price_multiplier en el request, podemos actualizarlo (opcional)
                    if (isset($itemData['price_multiplier'])) {
                        $productStore->price_multiplier = $itemData['price_multiplier'];
                    }

                    $mult = $productStore->price_multiplier ?? 1.0;

                    // Determinar la base: preferimos el unit_price enviado en la compra, si existe
                    if (isset($itemData['unit_price'])) {
                        $basePrice = $itemData['unit_price'];
                    } else {
                        // si no vino desde la compra, usamos el precio de la bodega (product_branch)
                        $basePrice = $productBranch->unit_price ?? 0;
                    }

                    $productStore->unit_price = round(($basePrice * $mult), 2);
                    // No modificamos quantity aquí (las compras afectan product_branches), solo actualizamos precio
                    $productStore->last_update = now();
                    $productStore->branch_id = $branchId;
                    $productStore->save();
                } catch (\Throwable $e) {
                    // No detener todo el proceso por un error de sincronización de precios; registrar y continuar
                    // puedes loggear $e->getMessage() si quieres depurar
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
