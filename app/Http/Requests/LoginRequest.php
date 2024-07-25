<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class LoginRequest extends FormRequest {
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
   // LoginRequest.php

public function rules(): array
{
    return [
        'email' => 'required|string',
        'password' => 'required|string|min:5|max:10',
    ];
}

public function validate()
{
    $rules = $this->rules();

    $loginField = filter_var($this->input('email'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

    $rules[$loginField] = 'exists:users,' . $loginField;

    return validator($this->all(), $rules);
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
