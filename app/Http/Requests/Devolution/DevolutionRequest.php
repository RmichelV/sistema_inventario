<?php

namespace App\Http\Requests\Devolution;

use Illuminate\Foundation\Http\FormRequest;

class DevolutionRequest extends FormRequest
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
    {  $saleItem = \App\Models\Sale_Item::find($this->input('sale_item_id'));

        // Obtiene la cantidad vendida, o 0 si no se encuentra.
        $maxQuantity = $saleItem ? $saleItem->quantity_products : 1000;

        return [
            // ✅ Asegúrate de que sale_item_id exista y sea válido.
            'sale_item_id' => ['required', 'integer', 'exists:sale_items,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:' . $maxQuantity],
            'reason' => ['required', 'string', 'max:255'],
            'refund_amount' => ['required', 'numeric', 'min:0'],
        ];
    }
}
