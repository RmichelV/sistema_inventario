<?php

namespace App\Http\Requests\Products;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
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
                'products.*.name' => ['string', 'max:255'],
                'products.*.code' => ['required', 'string', 'max:255', 'unique:products,code'],
                'products.*.img_product' => ['required', 'image', 'max:2048'],
                'products.*.quantity_in_stock' => ['required', 'numeric', 'min:0'],
                'products.*.units_per_box' => ['required', 'numeric', 'min:0'],
        ];
    }
     public function messages(): array
    {
        return [
            
            'products.*.code.required' => 'El campo :attribute es obligatorio.',
            'products.*.code.unique' => 'El producto ya ha sido registrado.',
           
        ];
    }
}
