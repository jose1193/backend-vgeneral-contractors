<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;


class ScopeSheetZoneRequest extends FormRequest
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
    // Base rules
    $rules = [
        // 'scope_sheet_id' is required only for POST (store) and should exist in the scope_sheets table
        'scope_sheet_id' => $this->isMethod('post')
            ? ['required', 'integer', 'exists:scope_sheets,id']
            : ['nullable', 'integer', 'exists:scope_sheets,id'],

        // 'zone_id' is required for POST and should exist in the zones table; optional for PUT/PATCH
        'zone_id' => $this->isMethod('post')
            ? ['required', 'integer', 'exists:zones,id']
            : ['nullable', 'integer', 'exists:zones,id'],
        'zone_notes' => 'nullable|string|max:255',
    ];

    // Additional rules for update if needed
    if ($this->isMethod('put') || $this->isMethod('patch')) {
        // Add any additional validation rules for updates if needed
        // For example, you might want to ensure uniqueness or additional constraints
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
