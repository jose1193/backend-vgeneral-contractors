<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Exceptions\HttpResponseException;

class AuthRequest extends FormRequest
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
            'email' => ['required', 'string'],
            'password' => ['required', 'string', 'min:5', 'max:10'],
        ];
    }

    /**
     * Validate the auth request.
     */
    public function validate()
    {
        $rules = $this->rules();

        $loginField = filter_var($this->input('email'), FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $rules[$loginField] = 'exists:users,' . $loginField;

        return validator($this->all(), $rules);
    }

    /**
     * Get custom error messages for the request.
     */
    public function messages(): array
    {
        return [
            'required' => 'The :attribute field is required.',
            'string' => 'The :attribute must be a string.',
            'email.exists' => 'These credentials do not match our records.',
            'username.exists' => 'These credentials do not match our records.',
            'password.min' => 'The password must be at least :min characters.',
            'password.max' => 'The password may not be greater than :max characters.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
    protected function failedValidation(Validator $validator): void
    {
        // Log the validation errors
        Log::warning('Auth Request Validation Failed', [
            'errors' => $validator->errors()->toArray(),
            'input' => $this->except('password')  // Log input except password for security
        ]);

        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation errors',
            'errors' => $validator->errors()
        ], 422));
    }
}