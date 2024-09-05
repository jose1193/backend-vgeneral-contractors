<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use App\Models\User;

class FileEsxRequest extends FormRequest
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
    return [
        'file_name' => 'nullable|max:255',
        'file_path' => 'required|file|max:10048',
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
