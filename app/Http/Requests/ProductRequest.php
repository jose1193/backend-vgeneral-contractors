<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

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
    // Obtén el ID del producto de la ruta si está presente
    $productId = $this->route('product') ? $this->route('product')->id : null;

    return [
        'product_category_id' => [
            'required',
            'integer',
            Rule::exists('category_products', 'id'), 
        ],
        'product_name' => 'required|string|max:255',
        'product_description' => 'nullable|string|max:500', // Ajusta el max length según lo que necesites
        'price' => 'nullable|numeric|min:0',
        'unit' => 'nullable|string|max:255',
        'order_position' => 'nullable|integer|min:0',
    ];
}
    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'errors'    => $validator->errors()
        ], 422));
    }
}
