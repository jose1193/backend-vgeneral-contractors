<?php

namespace App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use App\Models\CategoryProduct;

class CategoryProductRequest extends FormRequest
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
    // Verifica si el ID de la categoría está presente en la solicitud
    $isUpdateRequest = $this->route('product-category') !== null;

    //\Log::info("Is update request: " . ($isUpdateRequest ? 'Yes' : 'No'));
    //\Log::info("Category product name being validated: " . $this->input('category_product_name'));

    return [
        'category_product_name' => [
            'required',
            'string',
            'max:255',
            function ($attribute, $value, $fail) use ($isUpdateRequest) {
                if ($isUpdateRequest) {
                    // Si es una solicitud de actualización, verificar si la categoría ya existe
                    $categoryId = $this->route('product-category')->id;

                    if (CategoryProduct::where('category_product_name', $value)
                        ->where('id', '!=', $categoryId)
                        ->exists()) {
                        $fail('The category name has already been used by another category.');
                    }
                } else {
                    // Si es una solicitud de registro, verificar si la categoría ya existe
                    if (CategoryProduct::where('category_product_name', $value)->exists()) {
                        $fail('The category name has already been registered.');
                    }
                }
            }
        ],
    ];
}


    public function failedValidation(Validator $validator)

    {

        throw new HttpResponseException(response()->json([

            'success'   => false,

            'message'   => 'Validation errors',

            'errors'      => $validator->errors()

        ], 422));

    }
}
