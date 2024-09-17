<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Http\Exceptions\HttpResponseException;

class AllianceCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Asegúrate de manejar correctamente la autorización
    }

    public function rules(): array
    {
        $rules = [
            'alliance_company_name' => ['string', 'max:255'],
            'signature_path' => ['string'], 
            'email' => ['email', 'max:255'],
            'phone' => ['string', 'max:20'],
            'address' => ['string', 'max:255'],
            'website' => ['nullable', 'url'],
        ];

        if ($this->isMethod('post')) {
            $this->makeFieldsRequired($rules);
        }

        return $rules;
    }

    private function makeFieldsRequired(array &$rules): void
    {
        foreach ($rules as $field => &$fieldRules) {
            if ($field !== 'website') {
                array_unshift($fieldRules, 'required');
            }
        }
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
