<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use App\Models\Claim;
use App\Models\ClaimAgreementPreview;
class ClaimAgreementPreviewRequest extends FormRequest
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
                        // Verificar la existencia del claim_id en claim_agreement_previews
                        $exists = ClaimAgreementPreview::where('claim_id', $claim->id)->exists();

                        if ($exists) {
                            $fail('A claim agreement preview for this claim already exists.');
                        }
                    } else {
                        $fail('The selected claim is invalid.');
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
