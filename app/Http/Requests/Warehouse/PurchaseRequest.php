<?php

namespace App\Http\Requests\Warehouse;

use Illuminate\Foundation\Http\FormRequest;

class PurchaseRequest extends FormRequest
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
            'purchase_date' => ['required', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.purchase_quantity' => ['required', 'numeric', 'min:1'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.total_price' => ['nullable', 'numeric', 'min:0'],
        ];
    }
    public function messages(): array
    {
        return [
            'purchase_date.required' => 'Debe elegir una fecha de compra.',
            'purchase_date.date' => 'La fecha de compra no es válida.',
            'items.required' => 'Debe agregar al menos un producto a la compra.',
            'items.array' => 'Los productos de compra deben ser una lista válida.',
            'items.min' => 'La compra debe contener al menos un producto.',
            'items.*.product_id.required' => 'Debe elegir un producto para cada artículo de la lista.',
            'items.*.product_id.exists' => 'El producto seleccionado no es válido.',
            'items.*.purchase_quantity.required' => 'Debe ingresar una cantidad de compra para cada producto.',
            'items.*.purchase_quantity.numeric' => 'La cantidad de compra debe ser un número.',
            'items.*.purchase_quantity.min' => 'La cantidad de compra no puede ser menor a 1.',
        ];
    }

}
