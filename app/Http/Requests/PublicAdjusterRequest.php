<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\PublicAdjuster;
use App\Models\User;
use Illuminate\Http\Request;

class PublicAdjusterRequest extends FormRequest
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
    // Determina si es una solicitud de actualización basada en la presencia del UUID
    $isUpdateRequest = $this->route('uuid') !== null;

    return [
        'user_id' => [
            'required',
            'integer',
            'exists:users,id',
            function ($attribute, $value, $fail) {
            $user = User::find($value);
            if (!$user || !$user->hasRole('Public Adjuster')) {
            $fail('The selected user must be an Public Adjuster.');
            }
            },
            
        ],
        'public_company_id' => [
            'required',
            'integer',
             'exists:public_companies,id'
            // Añade cualquier otra validación necesaria para public_company_id
            //function ($attribute, $value, $fail) use ($isUpdateRequest) {
                //if ($isUpdateRequest) {
                    // Obtiene el ID del ajustador actual basado en el UUID
                    //$currentAdjusterId = PublicAdjuster::where('uuid', $this->route('uuid'))->value('id');

                    //if ($currentAdjusterId) {
                        // Verifica si el mismo `user_id` y `public_company_id` están ya asignados a otro ajustador
                        //$existingAdjuster = PublicAdjuster::where('public_company_id', $value)
                            //->where('user_id', $this->input('user_id'))
                            //->where('id', '!=', $currentAdjusterId)
                            //->exists();

                        //if ($existingAdjuster) {
                            //$fail('The user is already assigned to another adjuster in this company.');
                        //}
                    //} else {
                        //$fail('Could not find the adjuster to update.');
                    //}
                //}
               
            //},
        ],
    ];
}


    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'errors'    => $validator->errors()
        ], 422));
    }
}
