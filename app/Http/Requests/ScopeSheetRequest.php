<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;


class ScopeSheetRequest extends FormRequest
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
            // 'claim_id' is required only for POST (store)
            'claim_id' => $this->isMethod('post') ? ['required', 'integer'] : ['nullable', 'integer'],
            
            'scope_sheet_description' => ['nullable', 'string', 'max:255'],
        ];

      
        if ($this->isMethod('put') || $this->isMethod('patch')) {
           
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
