<?php

namespace App\Http\Requests\Store;

use Illuminate\Foundation\Http\FormRequest;

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
            'sale_date' => ['required', 'date'], // ¡Así es como debe ser!
            'pay_type' => ['required', 'string'],
            'final_price' => ['required', 'numeric', 'min:0'],
            'customer_name' => ['required', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity_from_warehouse' => ['nullable', 'numeric', 'min:0'],
            'items.*.quantity_from_store' => ['nullable', 'numeric', 'min:0'],
            'items.*.selected_price' => 'required|numeric|min:0',
        ];
    }
    public function messages(): array
    {
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
        ];
    }
}
