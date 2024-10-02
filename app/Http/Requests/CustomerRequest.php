<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use App\Models\Customer;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:40', 'regex:/^[a-zA-Z\s]+$/'],
            'last_name' => ['nullable', 'string', 'max:40', 'regex:/^[a-zA-Z\s]+$/'],
            'email' => $this->getEmailRules(),
            'cell_phone' => ['nullable', 'string', 'max:20'],
            'home_phone' => ['nullable', 'string', 'max:20'],
            'occupation' => ['nullable', 'string', 'max:40', 'regex:/^[a-zA-Z\s]+$/'],
            'signature_data' => ['nullable', 'string'],
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules = array_map(fn($rule) => array_merge(['sometimes'], $rule), $rules);
        }

        return $rules;
    }

    private function getEmailRules(): array
    {
        $rules = ['required', 'email', 'max:255'];

        if ($this->isMethod('post')) {
            $rules[] = Rule::unique('customers', 'email');
        } elseif ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules[] = Rule::unique('customers', 'email')->ignore($this->route('uuid'), 'uuid');
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'required' => 'The :attribute field is required.',
            'string' => 'The :attribute must be a string.',
            'max' => 'The :attribute may not be greater than :max characters.',
            'email' => 'The :attribute must be a valid email address.',
            'regex' => 'The :attribute format is invalid.',
            'email.unique' => 'This email is already registered.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422)
        );
    }
}