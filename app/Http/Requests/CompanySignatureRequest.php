<?php

declare(strict_types=1);

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
     * @return array<string, array<int, string|Rule>>
     */
    public function rules(): array
    {
        return [
            'company_name' => $this->getFieldRules('string', 255),
            'signature_path' => $this->getFieldRules('string'),
            'email' => $this->getFieldRules('email', 255),
            'phone' => $this->getFieldRules('string', 20),
            'address' => $this->getFieldRules('string', 255),
            'website' => $this->getFieldRules('string', 255,),
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'required' => 'The :attribute field is required.',
            'string' => 'The :attribute must be a string.',
            'max' => 'The :attribute may not be greater than :max characters.',
            'email' => 'The :attribute must be a valid email address.',
            'url' => 'The :attribute must be a valid URL.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     * @throws HttpResponseException
     */
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

    /**
     * Get the validation rules for a field based on the request method.
     *
     * @param string $type
     * @param int|null $max
     * @param bool $nullable
     * @return array<int, string|Rule>
     */
    private function getFieldRules(string $type, ?int $max = null, bool $nullable = false): array
    {
        $rules = [$type];

        if ($max !== null) {
            $rules[] = "max:$max";
        }

        if ($this->isMethod('post')) {
            array_unshift($rules, $nullable ? 'nullable' : 'required');
        } elseif ($this->isMethod('put') || $this->isMethod('patch')) {
            array_unshift($rules, 'sometimes', 'nullable');
        }

        return $rules;
    }
}