<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class InsuranceCompanyRequest extends FormRequest
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
            'insurance_company_name' => $this->getInsuranceCompanyNameRules(),
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],
            'prohibited_alliances' => ['nullable', 'array'],
            'prohibited_alliances.*' => ['integer', 'exists:alliance_companies,id'],
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            // Aplicar 'sometimes' para PUT o PATCH
            $rules = array_map(fn($rule) => array_merge(['sometimes'], (array)$rule), $rules);
        }

        return $rules;
    }

    /**
     * Get the validation rules for 'insurance_company_name' with uniqueness check.
     */
    private function getInsuranceCompanyNameRules(): array
    {
        $rules = ['required', 'string', 'max:255'];

        if ($this->isMethod('post')) {
            $rules[] = Rule::unique('insurance_companies', 'insurance_company_name');
        } elseif ($this->isMethod('put') || $this->isMethod('patch')) {
            $currentCompany = $this->route('insurance-company');
            
            // Verificar si el nombre no ha cambiado
            if ($currentCompany && $currentCompany->insurance_company_name !== $this->input('insurance_company_name')) {
                $rules[] = Rule::unique('insurance_companies', 'insurance_company_name')->ignore($currentCompany->id);
            }
        }

        return $rules;
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
            'insurance_company_name.unique' => 'This insurance company name is already registered.',
            'email' => 'The :attribute must be a valid email address.',
            'url' => 'The :attribute must be a valid URL.',
            'prohibited_alliances.*.exists' => 'The selected prohibited alliance is invalid.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        // Log the validation errors
        Log::warning('Insurance Company Request Validation Failed', [
            'errors' => $validator->errors()->toArray(),
            'input' => $this->all()
        ]);

        // Return JSON response with errors
        $response = response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors()
        ], 422);

        $response->send();
        exit;
    }
}