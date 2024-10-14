<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ClaimRequest extends FormRequest
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
        $isStoreRoute = $this->is('api/claim/store');

        return [
            'property_id' => $this->getFieldRules('exists:properties,id', null, !$isStoreRoute),
            'signature_path_id' => $this->getFieldRules('exists:company_signatures,id', null, true),
            'type_damage_id' => $this->getFieldRules('exists:type_damages,id', null, !$isStoreRoute),
            'user_id_ref_by' => $this->getFieldRules('integer', null, true),
            'user_id_ref_by.*' => 'exists:users,id',
            'policy_number' => $this->getFieldRules('string', 255, !$isStoreRoute),
            'claim_internal_id' => $this->getFieldRules('string', 255, true),
            'date_of_loss' => $this->getFieldRules('string', 255, true),
            'description_of_loss' => $this->getFieldRules('string', null, true),
            'claim_date' => $this->getFieldRules('string', 255, true),
            'claim_status' => $this->getFieldRules('integer', 255, true),
            'damage_description' => $this->getFieldRules('string', 255, true),
            'scope_of_work' => $this->getFieldRules('string', null, true),
            'customer_reviewed' => $this->getFieldRules('boolean', null, true),
            'claim_number' => $this->getFieldRules('string', 255, true),
            'number_of_floors' => $this->getFieldRules('integer', 10, true),
            'alliance_company_id' => $this->getFieldRules('integer', null, true),
            'service_request_id' => $this->getArrayFieldRules($isStoreRoute, 1, 10, 'exists:service_requests,id'),
            'cause_of_loss_id' => $this->getArrayFieldRules($isStoreRoute, 1, 10, 'exists:cause_of_losses,id'),
            'insurance_adjuster_id' => array_merge(
                $this->getFieldRules('integer', null, true),
                ['exists:users,id']
            ),
            'public_adjuster_id' => [
            'integer',
            'exists:users,id',
            function ($attribute, $value, $fail) {
            $this->validatePublicAdjusterUser($attribute, $value, $fail);
            },
            ],
            'public_company_id' => $this->getFieldRules('integer', null, true),
            'public_company_id.*' => 'exists:public_companies,id',
            'insurance_company_id' => $this->getFieldRules('integer', null, true),
            'insurance_company_id.*' => 'exists:insurance_companies,id',
            'work_date' => $this->getFieldRules('string', 255, true),
            'technical_user_id' => ['nullable', 'array'],
            'technical_user_id.*' => [
                'integer',
                'exists:users,id',
                function ($attribute, $value, $fail) {
                    $this->validateTechnicalUser($attribute, $value, $fail);
                },
            ],
            'day_of_loss_ago' => $this->getFieldRules('string', 255, true),
            'never_had_prior_loss' => $this->getFieldRules('boolean', null, true),
            'has_never_had_prior_loss' => $this->getFieldRules('boolean', null, true),
            'amount_paid' => $this->getFieldRules('numeric', null, true),
            'description' => $this->getFieldRules('string', null, true),
            'mortgage_company_name' => $this->getFieldRules('string', 255, true),
            'mortgage_company_phone' => $this->getFieldRules('string', 255, true),
            'mortgage_loan_number' => $this->getFieldRules('string', 255, true),
            'affidavit' => $this->getFieldRules('array', null, true),
            'affidavit.mortgage_company_name' => $this->getFieldRules('string', 255, true),
            'affidavit.mortgage_company_phone' => $this->getFieldRules('string', 255, true),
            'affidavit.mortgage_loan_number' => $this->getFieldRules('string', 255, true),
            'affidavit.description' => $this->getFieldRules('string', null, true),
            'affidavit.amount_paid' => $this->getFieldRules('numeric', null, true),
            'affidavit.day_of_loss_ago' => $this->getFieldRules('string', 255, true),
            'affidavit.never_had_prior_loss' => $this->getFieldRules('boolean', null, true),
            'affidavit.has_never_had_prior_loss' => $this->getFieldRules('boolean', null, true),
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
            'integer' => 'The :attribute must be an integer.',
            'numeric' => 'The :attribute must be a number.',
            'boolean' => 'The :attribute must be true or false.',
            'max' => 'The :attribute may not be greater than :max characters.',
            'email' => 'The :attribute must be a valid email address.',
            'exists' => 'The selected :attribute is invalid.',
            'array' => 'The :attribute must be an array.',
            'min' => 'The :attribute must have at least :min items.',
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
        // Log the validation errors
        Log::warning('Claim Request Validation Failed', [
            'errors' => $validator->errors()->toArray(),
            'input' => $this->all()
        ]);
        
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422)
        );
    }

    /**
     * Validate that the user has the Technical Services role.
     *
     * @param string $attribute
     * @param mixed $value
     * @param callable $fail
     */
    protected function validateTechnicalUser($attribute, $value, $fail): void
    {
        $user = User::find($value);
        if (!$user || !$user->hasRole('Technical Services')) {
            $fail('The selected user must have the Technical Services role.');
        }
    }

    protected function validatePublicAdjusterUser($attribute, $value, $fail): void
    {
        $user = User::find($value);
        if (!$user || !$user->hasRole('Public Adjuster')) {
            $fail('The selected user must have the Public Adjuster role.');
        }
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

    /**
     * Get the validation rules for an array field.
     *
     * @param bool $isRequired
     * @param int|null $min
     * @param int|null $max
     * @param string $itemRules
     * @return array<int, string|Rule>
     */
    private function getArrayFieldRules(bool $isRequired, ?int $min, ?int $max, string $itemRules): array
    {
        $rules = ['array'];

        if ($isRequired) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }

        if ($min !== null) {
            $rules[] = "min:$min";
        }

        if ($max !== null) {
            $rules[] = "max:$max";
        }

        return $rules;
    }
}
