<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use App\Models\Claim;
use App\Models\ClaimAgreementFull;

class ClaimAgreementFullRequest extends FormRequest
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
    $isStoreRoute = $this->is('api/claim-agreement/store');

    return [
        'claim_uuid' => [
            'required',
            'string',
            'max:255',
            function ($attribute, $value, $fail) use ($isStoreRoute) {
                if ($isStoreRoute) {
                    // Buscar el claim por UUID
                    $claim = Claim::where('uuid', $value)->first();

                    if ($claim) {
                        // Verificar la existencia del claim_id en fulls
                        $exists = ClaimAgreementFull::where('claim_id', $claim->id)
                            ->where('agreement_type', $this->agreement_type) // Verify the type as well
                            ->exists();

                        if ($exists) {
                            $fail('A claim agreement full with this agreement type already exists.');
                        }
                    } else {
                        $fail('The selected claim is invalid.');
                    }
                }
            }
        ],

        'agreement_type' => [
            $isStoreRoute ? 'required' : 'nullable',
            'string',
            'in:Agreement,Agreement Full',
            function ($attribute, $value, $fail) {
                // Check for duplicate agreement_type for the same claim_uuid
                $claim = Claim::where('uuid', $this->claim_uuid)->first();

                if ($claim) {
                    $exists = ClaimAgreementFull::where('claim_id', $claim->id)
                        ->where('agreement_type', $value)
                        ->exists();

                    if ($exists) {
                        $fail('A claim agreement full with this agreement type already exists.');
                    }
                }
            }
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
