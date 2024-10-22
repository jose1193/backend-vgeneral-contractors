<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class AllianceCompanyRequest extends FormRequest
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
            'alliance_company_name' => $this->getAllianceCompanyNameRules(),
            'signature_path' => ['nullable', 'string'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'string', 'url', 'max:255'],
        ];

        if ($this->isMethod('post')) {
            // Make fields required for POST requests
            $this->makeFieldsRequired($rules);
        } elseif ($this->isMethod('put') || $this->isMethod('patch')) {
            // Apply 'sometimes' for PUT or PATCH requests
            $rules = array_map(fn($rule) => array_merge(['sometimes'], (array)$rule), $rules);
        }

        return $rules;
    }

    /**
     * Get the validation rules for 'alliance_company_name' with uniqueness check.
     */
    private function getAllianceCompanyNameRules(): array
    {
        $rules = ['string', 'max:255'];

        if ($this->isMethod('post')) {
            $rules[] = Rule::unique('alliance_companies', 'alliance_company_name');
        } elseif ($this->isMethod('put') || $this->isMethod('patch')) {
            $currentCompany = $this->route('alliance-company');

            // Check if name has changed
            if ($currentCompany && $currentCompany->alliance_company_name !== $this->input('alliance_company_name')) {
                $rules[] = Rule::unique('alliance_companies', 'alliance_company_name')->ignore($currentCompany->id);
            }
        }

        return $rules;
    }

    /**
     * Make specific fields required for POST requests.
     */
    private function makeFieldsRequired(array &$rules): void
    {
        $requiredFields = [
            'alliance_company_name',
            'email',
            'phone',
            'address'
        ];

        foreach ($requiredFields as $field) {
            if (isset($rules[$field])) {
                array_unshift($rules[$field], 'required');
            }
        }
    }

    /**
     * Get custom error messages for the request.
     */
    public function messages(): array
    {
        return [
            'required' => 'The :attribute field is required.',
            'string' => 'The :attribute must be a string.',
            'max' => 'The :attribute may not be greater than :max characters.',
            'alliance_company_name.unique' => 'This alliance company name is already registered.',
            'email' => 'The :attribute must be a valid email address.',
            'url' => 'The :attribute must be a valid URL.',
            'phone.max' => 'The phone number may not be greater than :max characters.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        // Log the validation errors
        Log::warning('Alliance Company Request Validation Failed', [
            'errors' => $validator->errors()->toArray(),
            'input' => $this->all()
        ]);

        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors()
        ], 422));
    }
}