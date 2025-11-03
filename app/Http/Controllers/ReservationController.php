<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Product_store;
use App\Models\Reservation_item;
use App\Models\Usd_exchange_rate;
use App\Models\product_branch as ProductBranch;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReservationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $authUser = auth()->user();
        $branchId = $authUser->branch_id ?? null;

        // Filtrar reservaciones por la sucursal del usuario
        $reservations = Reservation::with('customer')
            ->where('branch_id', $branchId)
            ->latest()
            ->get();

        $reservationsFormatted = collect($reservations)->map(function ($reservation) {
            return [
                'id' => $reservation->id,
                'customer_name' => $reservation->customer->name ?? 'Sin cliente',
                'total_amount' => $reservation->total_amount,
                'advance_amount' => $reservation->advance_amount,
                'rest_amount' => $reservation->rest_amount,
                'pay_type' => $reservation->pay_type,
                'created_at' => $reservation->created_at,
            ];
        });

        $branches = \App\Models\branch::all();
        $currentBranch = null;
        if ($authUser && $authUser->branch_id) {
            $currentBranch = $branches->firstWhere('id', $authUser->branch_id);
        }

        return Inertia::render('Reservations/Index', [
            'reservations' => $reservationsFormatted,
            'branches' => $branches,
            'currentBranch' => $currentBranch,
            'currentUser' => $authUser,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $authUser = auth()->user();
        $branchId = $authUser->branch_id ?? null;

        // Obtener todos los clientes
        $customers = Customer::all();

        // Obtener solo productos de tienda (product_stores) de la sucursal actual
        $productStores = Product_store::where('branch_id', $branchId)->get();

        // Mapear productos con información de producto
        $productStoresFormatted = collect($productStores)->map(function ($ps) {
            $product = Product::find($ps->product_id);
            return [
                'id' => $ps->id,
                'product_id' => $ps->product_id,
                'product_name' => $product?->name ?? 'Producto no encontrado',
                'product_code' => $product?->code ?? '',
                'quantity' => $ps->quantity,
                'unit_price' => $ps->unit_price,
            ];
        });

        // Obtener productos en bodega para la sucursal (product_branches)
        $productBranches = ProductBranch::with('product')
            ->where('branch_id', $branchId)
            ->get();

        return Inertia::render('Reservations/create', [
            'customers' => $customers,
            'productStores' => $productStoresFormatted,
            'productBranches' => $productBranches,
            'usd_exchange_rate' => Usd_exchange_rate::find(1),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'pay_type' => 'required|in:Efectivo,Qr,Dolares',
            'advance_amount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:product_stores,id',
            'items.*.quantity_from_warehouse' => 'nullable|numeric|min:0',
            'items.*.quantity_from_store' => 'nullable|numeric|min:0',
            'items.*.selected_price' => 'required|numeric|min:0',
            'items.*.selected_min_type' => 'required|in:usd,bs,bs_sale',
        ]);

        // Obtener tasa de cambio
        $exchange_rate = Usd_exchange_rate::find(1);
        $exchangeRateValue = $exchange_rate->exchange_rate ?? 1;

        // Validar que cada precio sea mayor o igual al mínimo según su tipo
        foreach ($request->items as $index => $item) {
            $productStore = Product_store::findOrFail($item['product_id']);
            $quantityWarehouse = floatval($item['quantity_from_warehouse'] ?? 0);
            $quantityStore = floatval($item['quantity_from_store'] ?? 0);
            $quantity = $quantityWarehouse + $quantityStore;
            $selectedPrice = floatval($item['selected_price']);
            $selectedType = $item['selected_min_type'];

            // Calcular mínimo según el tipo seleccionado
            $unitPrice = $productStore->unit_price;
            $totalPriceUsd = $unitPrice * $quantity;
            $totalPriceBs = $totalPriceUsd * $exchangeRateValue;
            $totalPriceSale = $totalPriceBs * 1.1;

            $minimumPrice = match($selectedType) {
                'usd' => $totalPriceUsd,
                'bs' => $totalPriceBs,
                'bs_sale' => $totalPriceSale,
                default => $totalPriceBs,
            };

            if ($selectedPrice < $minimumPrice) {
                $currency = $selectedType === 'usd' ? '$' : 'Bs.';
                return redirect()->back()->withErrors([
                    "items.$index.selected_price" => "El precio debe ser mayor o igual a $currency " . number_format($minimumPrice, 2)
                ])->withInput();
            }
        }
        
        \Illuminate\Support\Facades\DB::beginTransaction();

        try {
            // Calcular total de la reservación
            $totalAmount = 0;
            $itemsData = [];
            $authUser = auth()->user();
            $branchId = $authUser->branch_id ?? null;

            foreach ($request->items as $item) {
                $productStore = Product_store::findOrFail($item['product_id']);
                $quantityWarehouse = floatval($item['quantity_from_warehouse'] ?? 0);
                $quantityStore = floatval($item['quantity_from_store'] ?? 0);
                $quantity = $quantityWarehouse + $quantityStore;
                $selectedPrice = floatval($item['selected_price']);
                $selectedType = $item['selected_min_type'];
                
                // Guardar el precio tal como está ingresado (sin conversión)
                // El tipo de cambio se guarda solo como referencia histórica
                $totalAmount += $selectedPrice;
                
                $itemsData[] = [
                    'product_id' => $productStore->product_id,
                    'quantity_products' => $quantity,
                    'quantity_from_warehouse' => $quantityWarehouse,
                    'quantity_from_store' => $quantityStore,
                    'total_price' => $selectedPrice,
                    'exchange_rate' => $exchange_rate->exchange_rate,
                ];
            }

            // Calcular saldo restante
            $advanceAmount = $validated['advance_amount'];
            $restAmount = $totalAmount - $advanceAmount;

            // Crear la reservación
            $reservation = Reservation::create([
                'customer_id' => $validated['customer_id'],
                'total_amount' => $totalAmount,
                'advance_amount' => $advanceAmount,
                'rest_amount' => $restAmount,
                'exchange_rate' => $exchange_rate->exchange_rate,
                'pay_type' => $validated['pay_type'],
                'branch_id' => $branchId,
            ]);

            // Crear los items de la reservación y actualizar stock
            $currentDate = now()->toDateString();
            foreach ($itemsData as $itemData) {
                Reservation_item::create(array_merge($itemData, ['reservation_id' => $reservation->id]));
                
                // Obtener el producto para las validaciones
                $productId = $itemData['product_id'];
                $quantityWarehouse = $itemData['quantity_from_warehouse'];
                $quantityStore = $itemData['quantity_from_store'];
                
                // Obtener el stock en bodega por sucursal (product_branches)
                $productInBranch = ProductBranch::where('branch_id', $branchId)
                    ->where('product_id', $productId)
                    ->first();

                // Obtener el stock en tienda para la sucursal
                $productInStore = Product_store::where('product_id', $productId)
                    ->where('branch_id', $branchId)
                    ->first();

                // Actualizar stock en bodega (product_branches)
                if ($quantityWarehouse > 0 && $productInBranch) {
                    $productInBranch->decrement('quantity_in_stock', $quantityWarehouse);
                    $productInBranch->update(['last_update' => $currentDate]);
                }

                // Actualizar stock en tienda (product_stores)
                if ($quantityStore > 0 && $productInStore) {
                    $productInStore->decrement('quantity', $quantityStore);
                    $productInStore->update(['last_update' => $currentDate]);
                }
            }

            \Illuminate\Support\Facades\DB::commit();

            return redirect()->route('rreservations.index')->with('success', 'Reservación registrada exitosamente.');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::rollBack();
            return redirect()->back()->with('error', 'Ocurrió un error al registrar la reservación. Por favor, inténtelo de nuevo.')->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $reservation = Reservation::with('customer')->findOrFail($id);
        $reservationItems = Reservation_item::with('product')->where('reservation_id', $id)->get();

        $itemsFormatted = collect($reservationItems)->map(function ($item) {
            return [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'product_name' => $item->product?->name ?? 'Producto no encontrado',
                'product_code' => $item->product?->code ?? '',
                'quantity_products' => $item->quantity_products,
                'total_price' => floatval($item->total_price),
                'exchange_rate' => floatval($item->exchange_rate),
            ];
        });

        $authUser = auth()->user();

        return Inertia::render('Reservations/show', [
            'reservation' => [
                'id' => $reservation->id,
                'customer' => $reservation->customer,
                'total_amount' => floatval($reservation->total_amount),
                'advance_amount' => floatval($reservation->advance_amount),
                'rest_amount' => floatval($reservation->rest_amount),
                'exchange_rate' => floatval($reservation->exchange_rate),
                'pay_type' => $reservation->pay_type,
                'created_at' => $reservation->created_at,
            ],
            'items' => $itemsFormatted,
            'currentUser' => $authUser,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reservation $reservation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reservation $reservation)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $reservation = Reservation::findOrFail($id);
            
            // Obtener la sucursal del usuario autenticado
            $authUser = auth()->user();
            $branchId = $authUser->branch_id ?? null;

            // Obtener todos los items ANTES de eliminar
            $reservationItems = Reservation_item::where('reservation_id', $reservation->id)->get();

            // Devolver TODOS los items a bodega (product_branches)
            $currentDate = now()->toDateString();
            foreach ($reservationItems as $item) {
                $quantityWarehouse = floatval($item->quantity_from_warehouse ?? 0);
                $quantityStore = floatval($item->quantity_from_store ?? 0);
                $totalQuantity = $quantityWarehouse + $quantityStore; // Total a devolver a bodega
                $productId = $item->product_id;

                // Devolver TODO el stock a bodega (product_branches)
                if ($totalQuantity > 0) {
                    $productInBranch = ProductBranch::where('branch_id', $branchId)
                        ->where('product_id', $productId)
                        ->first();

                    if ($productInBranch) {
                        $productInBranch->increment('quantity_in_stock', $totalQuantity);
                        $productInBranch->update(['last_update' => $currentDate]);
                    }
                }
            }

            // Eliminar la reservación (los items se eliminarán automáticamente por onDelete cascade)
            $reservation->delete();

            return back()->with('success', 'Reservación eliminada exitosamente y stock devuelto a bodega.');

        } catch (\Exception $e) {
            \Log::error('Error al eliminar reservación: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al eliminar la reservación.');
        }
    }
}
