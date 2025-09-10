<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;

//librerias
use Inertia\Inertia;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

//modelos
use App\Models\Product;
use App\Models\Product_store;
use App\Models\Usd_exchange_rate;
use App\Models\Sale_item;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sales = Sale::all();
        
        return Inertia::render('Sales/Index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {   
        $products = Product::all();
        $productStores=Product_store::all();
        $usd_exchange_rates = Usd_exchange_rate::find(1);

        return Inertia::render('Sales/create',[
            'products'=>$products,
            'productStores' => $productStores,
            'usd_exchange_rate'=>$usd_exchange_rates
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
   public function store(Request $request)
    {
        // 1. Validar los datos de la solicitud
        $validator = Validator::make($request->all(), [
            'product_id' => ['required', 'exists:products,id'],
            'quantity_products' => ['required', 'integer', 'min:1'],
            'sale_date' => ['required', 'date'],
            'pay_type' => ['required', 'string', 'in:Dolares,Bolivianos,Qr'],
            'final_price' => ['required', 'numeric', 'min:0'],
            'customer_name' => ['required', 'string', 'max:255'],
        ]);

        if ($validator->fails()) {
            throw ValidationException::withMessages($validator->messages()->toArray());
        }

        // Iniciar una transacción de base de datos
        DB::beginTransaction();

        try {
            $validatedData = $validator->validated();

            // 2. Obtener los productos en tienda y bodega
            $productInStore = Product_store::where('product_id', $validatedData['product_id'])->first();
            $productInWarehouse = Product::findOrFail($validatedData['product_id']);
            $quantityToSell = $validatedData['quantity_products'];

            // 3. Lógica para el descuento de stock
            $storeQuantity = $productInStore ? $productInStore->quantity : 0;
            $warehouseQuantity = $productInWarehouse->quantity_in_stock;

            // Verificar si hay suficiente stock en total
            if ($quantityToSell > ($storeQuantity + $warehouseQuantity)) {
                DB::rollBack();
                return redirect()->back()->withErrors(['quantity_products' => 'La cantidad solicitada supera el stock total disponible.']);
            }

            // Descontar primero del stock en la tienda
            if ($storeQuantity >= $quantityToSell) {
                // Stock de la tienda es suficiente
                $productInStore->decrement('quantity', $quantityToSell);
            } else {
                // Descontar todo el stock de la tienda y el resto de la bodega
                $remainingToSell = $quantityToSell - $storeQuantity;
                
                if ($productInStore) {
                    $productInStore->update(['quantity' => 0]);
                }

                $productInWarehouse->decrement('quantity_in_stock', $remainingToSell);
            }

            // 4. Crear el registro de la venta en la tabla `sales`
            $sale = Sale::create([
                'sale_code' => 'SALE-' . now()->format('YmdHis') . '-' . rand(100, 999),
                'customer_name' => $validatedData['customer_name'],
                'sale_date' => $validatedData['sale_date'],
                'pay_type' => $validatedData['pay_type'],
                'final_price' => $validatedData['final_price'],
            ]);

            // 5. Crear el registro del ítem de la venta en la tabla `sale_items`
            // El `sale_id` se asocia automáticamente a través de la relación.
            $sale->items()->create([
                'product_id' => $validatedData['product_id'],
                'quantity_products' => $validatedData['quantity_products'],
            ]);

            // 6. Si todo es exitoso, confirmar la transacción.
            DB::commit();

            return redirect()->route('sales.index')->with('success', 'Venta registrada con éxito.');

        } catch (\Exception $e) {
            // 7. Si algo falla, revertir la transacción.
            DB::rollBack();
            return redirect()->back()->withErrors(['general' => 'Ocurrió un error al registrar la venta. Por favor, inténtelo de nuevo.']);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sale)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        //
    }
}
