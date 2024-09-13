<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;


class CustomerSignatureRequest extends FormRequest
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
    $rules = [
        // Definición de reglas básicas
        'signature_data' => ['required'],
    ];

    // Verificar la ruta y el método
    if ($this->is('api/customer-signature/update')) {
        // Si la ruta es de actualización, `customer_id` no es obligatorio
        $rules['customer_id'] = ['nullable', 'integer'];
    } else if ($this->isMethod('POST')) {
        // Si es una solicitud POST (creación), `customer_id` debe ser obligatorio
        $rules['customer_id'] = ['required', 'integer', Rule::unique('customer_signatures')];
    }

    return $rules;
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
