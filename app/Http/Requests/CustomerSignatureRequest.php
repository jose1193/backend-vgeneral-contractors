<?php

declare(strict_types=1);

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
     * @return array<string, array<int, string|Rule>>
     */
    public function rules(): array
    {
        return [
            'customer_id' => $this->getFieldRules('integer'),
            'signature_data' => $this->getFieldRules('string'),
            // Add more fields as needed
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
            'integer' => 'The :attribute must be an integer.',
            'string' => 'The :attribute must be a string.',
            'unique' => 'The :attribute has already been taken.',
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
            if ($type === 'integer' && $this->input('customer_id')) {
                $rules[] = Rule::unique('customer_signatures', 'customer_id');
            }
        } elseif ($this->isMethod('put') || $this->isMethod('patch')) {
            array_unshift($rules, 'sometimes', 'nullable');
        }

        return $rules;
    }
}