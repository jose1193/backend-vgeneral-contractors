<?php

namespace App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use App\Models\User;

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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
 public function rules(): array
{
    $isStoreRoute = $this->is('api/claim/store');

    return [
        // Campos requeridos
        'property_id' => $isStoreRoute ? 'required|exists:properties,id' : 'sometimes|exists:properties,id',
        'signature_path_id' => $isStoreRoute ? 'nullable|exists:company_signatures,id' : 'sometimes|exists:company_signatures,id',
        'type_damage_id' => $isStoreRoute ? 'required|exists:type_damages,id' : 'sometimes|exists:type_damages,id',
        'user_id_ref_by' => 'nullable|integer|exists:users,id',
        'policy_number' => $isStoreRoute ? 'required|string|max:255' : 'sometimes|string|max:255',

        // Campos opcionales
        'claim_internal_id' => 'nullable|string|max:255',
        'date_of_loss' => 'nullable|string|max:255',
        'description_of_loss' => 'nullable',
        'claim_date' => 'nullable|string|max:255',
        'claim_status' => 'nullable|string|max:255',
        'damage_description' => 'nullable|string|max:255',
        'scope_of_work' => 'nullable',
        'customer_reviewed' => 'nullable|boolean',
        'claim_number' => 'nullable|string|max:255',
        'number_of_floors' => 'nullable|integer|max:10',

        // Alliance company (ID)
        'alliance_company_id' => 'nullable|integer',
        // Alliance company (array of IDs)
        //'alliance_company_id' => $isStoreRoute 
            //? ['nullable', 'array', 'min:1', 'max:2'] 
            //: ['nullable', 'array', 'max:2'],
        //'alliance_company_id.*' => 'integer|exists:alliance_companies,id',

         // Validación para el array de IDs de Service Request
        'service_request_id' => $isStoreRoute 
        ? ['required', 'array', 'min:1', 'max:10'] 
        : ['nullable', 'array', 'max:10'],

        // Validación para cada elemento del array
        'service_request_id.*' => 'integer|exists:service_requests,id',


        // Validación de otros roles específicos
        'insurance_adjuster_id' => [
            'nullable',
            'integer',
            'exists:users,id',
            function ($attribute, $value, $fail) {
                $user = User::find($value);
                if (!$user || !$user->hasRole('Insurance Adjuster')) {
                    $fail('The selected user must be an Insurance Adjuster.');
                }
            },
        ],
        'public_adjuster_id' => [
            'nullable',
            'integer',
            'exists:users,id',
            function ($attribute, $value, $fail) {
                $user = User::find($value);
                if (!$user || !$user->hasRole('Public Adjuster')) {
                    $fail('The selected user must be a Public Adjuster.');
                }
            },
        ],
        'public_company_id' => 'nullable|integer|exists:public_companies,id',
        'insurance_company_id' => 'nullable|integer|exists:insurance_companies,id',
        'work_date' => 'nullable|string|max:255',

        // Validación para usuarios técnicos
        'technical_user_id' => 'nullable|array',
        'technical_user_id.*' => [
            'integer',
            'exists:users,id',
            function ($attribute, $value, $fail) {
                $user = User::find($value);
                if (!$user || !$user->hasRole('Technical Services')) {
                    $fail('The selected user must have the Technical Services role.');
                }
            },
        ],

        // Campos adicionales opcionales
        'day_of_loss_ago' => 'nullable|string|max:255', 
        'never_had_prior_loss' => 'nullable|boolean', 
        'has_never_had_prior_loss' => 'nullable|boolean', 
        'amount_paid' => 'nullable|numeric|max:99999999.99', 
        'description' => 'nullable|string', 
        'mortgage_company_name' => 'nullable|string|max:255',
        'mortgage_company_phone' => 'nullable|string|max:255', 
        'mortgage_loan_number' => 'nullable|string|max:255', 

        // Validación para el campo affidavit
        'affidavit' => 'nullable|array',
        'affidavit.mortgage_company_name' => 'nullable|string|max:255',
        'affidavit.mortgage_company_phone' => 'nullable|string|max:255',
        'affidavit.mortgage_loan_number' => 'nullable|string|max:255',
        'affidavit.description' => 'nullable|string',
        'affidavit.amount_paid' => 'nullable|numeric|max:99999999.99',
        'affidavit.day_of_loss_ago' => 'nullable|string|max:255',
        'affidavit.never_had_prior_loss' => 'nullable|boolean',
        'affidavit.has_never_had_prior_loss' => 'nullable|boolean',
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
