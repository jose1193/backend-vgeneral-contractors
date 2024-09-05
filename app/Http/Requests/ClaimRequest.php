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
        'property_id' => $isStoreRoute ? 'required|exists:customer_properties,id' : 'sometimes|exists:customer_properties,id',
        'signature_path_id' => $isStoreRoute ? 'required|exists:company_signatures,id' : 'sometimes|exists:company_signatures,id',
        'type_damage_id' => $isStoreRoute ? 'required|exists:type_damages,id' : 'sometimes|exists:type_damages,id',
        'user_id_ref_by' => 'nullable|integer|exists:users,id',
        'policy_number' => $isStoreRoute ? 'required|string|max:255' : 'sometimes|string|max:255',

        // Campos opcionales
        'claim_internal_id' => 'nullable|string|max:255',
        'date_of_loss' => 'nullable|string|max:255',
        'claim_date' => 'nullable|string|max:255',
        'claim_status' => 'nullable|string|max:255',
        'damage_description' => 'nullable|string',
        'claim_number' => 'nullable|string|max:255',
        'number_of_floors' => 'nullable|integer|max:10',

        // Alliance company (array of IDs)
        'alliance_company_id' => $isStoreRoute 
            ? ['required', 'array', 'min:1', 'max:2'] 
            : ['nullable', 'array', 'max:2'],
        'alliance_company_id.*' => 'integer|exists:alliance_companies,id',
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
