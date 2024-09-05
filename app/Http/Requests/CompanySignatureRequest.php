<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;


class CompanySignatureRequest extends FormRequest
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
    $isStoreRoute = $this->is('api/claim/store');

    return [
        'company_name' => [
            $isStoreRoute ? 'required' : 'sometimes',
            'string',
            'max:255',
            $isStoreRoute
                ? Rule::unique('company_signatures')
                : Rule::unique('company_signatures')->ignore($this->route('company_signature')),
        ],
        'signature_path' => 'required',
        'email' => 'nullable|email|max:255',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:255',
        'website' => 'nullable|url|max:255',
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
