<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;


class PublicCompanyRequest extends FormRequest
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
         $dataId = $this->route('public-company') ? $this->route('public-company')->id : null;
        $isStoreRoute = $this->is('api/public-company/store');
    
        return [
         'public_company_name' => [
            $isStoreRoute ? 'required' : 'nullable',
            'string',
            'max:255',
            Rule::unique('public_companies', 'public_company_name')->ignore($dataId)
        ],
        'address' => 'nullable|string|max:255',
        'unit' => 'nullable|string|max:255',
        'phone' => 'nullable|string|max:255',
        'email' => 'nullable|string|email|max:255',
        'website' => 'nullable|string|url|max:255',
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