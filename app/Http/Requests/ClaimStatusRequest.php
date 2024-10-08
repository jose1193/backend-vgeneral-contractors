<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class ClaimStatusRequest extends FormRequest
{
    /**
     * The table name for the claim status model.
     */
    private const CLAIM_STATUS_TABLE = 'claim_status';

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
            'claim_status_name' => $this->getFieldRules('string', 255, false, self::CLAIM_STATUS_TABLE),
            'background_color' => $this->getFieldRules('string', 7, true),
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
            'unique' => 'The :attribute has already been taken.',
            'background_color.regex' => 'The background color must be a valid hex color code.',
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
     * @param string|null $uniqueTable
     * @return array<int, string|Rule>
     */
    private function getFieldRules(string $type, ?int $max = null, bool $nullable = false, ?string $uniqueTable = null): array
    {
        $rules = [$type];

        if ($max !== null) {
            $rules[] = "max:$max";
        }

        if ($this->isMethod('post')) {
            array_unshift($rules, $nullable ? 'nullable' : 'required');
            if ($uniqueTable) {
                $rules[] = Rule::unique($uniqueTable);
            }
        } elseif ($this->isMethod('put') || $this->isMethod('patch')) {
            array_unshift($rules, 'sometimes', 'nullable');
            if ($uniqueTable) {
                $rules[] = Rule::unique($uniqueTable)->ignore($this->route('claim_status'));
            }
        }

        if ($type === 'string' && $max === 7) {
            $rules[] = 'regex:/^#[a-fA-F0-9]{6}$/';
        }

        return $rules;
    }
}