<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Product; // Modelo para Bodega
use App\Models\Product_Store; // Modelo para Tienda (Nombre corregido: Product_Store)

class SaleRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'sale_date' => ['required', 'date'],
            'pay_type' => ['required', 'string'],
            'final_price' => ['required', 'numeric', 'min:0'],
            'customer_name' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.selected_price' => ['required', 'numeric', 'min:0'],

            // VALIDACIÓN DE STOCK EN BODEGA (Modelo Product)
            'items.*.quantity_from_warehouse' => [
                'nullable', 
                'numeric', 
                'min:0',
                function ($attribute, $value, $fail) {
                    $value = (float) $value;
                    // Solo validar si se solicita una cantidad positiva
                    if ($value <= 0) return; 

                    $index = explode('.', $attribute)[1];
                    $productId = $this->input("items.{$index}.product_id");

                    if ($productId) {
                        $product = Product::find($productId);
                        $availableStock = $product ? $product->quantity_in_stock : 0;
                        
                        if ($value > $availableStock) {
                            $fail("Stock insuficiente en Bodega para el ítem #".($index + 1).". Disponible: {$availableStock} unidades.");
                        }
                    }
                }
            ],
            
            // VALIDACIÓN DE STOCK EN TIENDA (Modelo Product_Store)
            'items.*.quantity_from_store' => [
                'nullable', 
                'numeric', 
                'min:0',
                function ($attribute, $value, $fail) {
                    $value = (float) $value;
                    // Solo validar si se solicita una cantidad positiva
                    if ($value <= 0) return; 

                    $index = explode('.', $attribute)[1];
                    $productId = $this->input("items.{$index}.product_id");

                    if ($productId) {
                        // CORREGIDO: Usando 'product_id' como nombre de columna
                        $productStore = Product_Store::where('product_id', $productId)->first(); 
                        $availableStock = $productStore ? $productStore->quantity : 0;
                        
                        // Si la cantidad solicitada excede el stock
                        if ($value > $availableStock) {
                            $fail("Stock insuficiente en Tienda para el producto #".($index + 1).". Disponible: {$availableStock} unidades.");
                        }
                    }
                }
            ],
        ];
    }
    
    public function messages(): array
    {
        // Los mensajes de error de stock (quantity_from_warehouse y quantity_from_store) son generados dinámicamente
        // en la función de validación anónima (Closure) para incluir el stock disponible.
        return [
            'sale_date.required' => 'Debe elegir una fecha de venta.',
            'pay_type.required' => 'Debe elegir un método de pago.',
            'final_price.required' => 'Debe llenar el campo de precio final.',
            'final_price.numeric' => 'El precio final debe ser un número.',
            'final_price.min' => 'El precio final no puede ser negativo.',
            'customer_name.required' => 'Debe ingresar el nombre del cliente.',
            'items.required' => 'Debe agregar al menos un producto a la venta.',
            'items.min' => 'La venta debe contener al menos un producto.',
            'items.*.product_id.required' => 'Debe elegir un producto para cada artículo de la lista.',
            'items.*.product_id.exists' => 'El producto seleccionado no es válido.',
            'items.*.quantity_from_warehouse.numeric' => 'La cantidad del almacén debe ser un número.',
            'items.*.quantity_from_warehouse.min' => 'La cantidad del almacén no puede ser negativa.',
            'items.*.quantity_from_store.numeric' => 'La cantidad de la tienda debe ser un número.',
            'items.*.quantity_from_store.min' => 'La cantidad de la tienda no puede ser negativa.',
            'items.*.selected_price.required' => 'Debe ingresar el precio cobrado por el producto.',
            'items.*.selected_price.numeric' => 'El precio cobrado debe ser un número.',
            'items.*.selected_price.min' => 'El precio cobrado no puede ser negativo.',
        ];
    }
}
