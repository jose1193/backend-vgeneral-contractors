<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class CreateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['nullable', 'string', 'max:255'],
            'username' => [
                'nullable',
                'string',
                'max:30',
                'regex:/^[a-zA-Z0-9_]+$/',
            ],
            'email' => ['required', 'string', 'email','max:255'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'generate_password' => ['nullable', 'boolean'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'address_2' => ['nullable', 'string', 'max:255'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'gender' => ['nullable', 'string', Rule::in(['male', 'female', 'other'])],
            'user_role' => ['nullable', 'integer', 'exists:roles,id'],
            'provider' => ['nullable', 'string', 'max:255'],
            'provider_id' => ['nullable', 'string', 'max:255'],
            'provider_avatar' => ['nullable', 'string', 'url', 'max:2048'],
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $rules = array_map(fn($rule) => array_merge(['sometimes'], $rule), $rules);
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'required' => 'The :attribute field is required.',
            'string' => 'The :attribute must be a string.',
            'max' => 'The :attribute may not be greater than :max characters.',
            'email' => 'The :attribute must be a valid email address.',
            'regex' => 'The :attribute format is invalid.',
            'email.unique' => 'This email is already registered.',
            'username.unique' => 'This username is already taken.',
            'username.regex' => 'The username may only contain letters, numbers, and underscores.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'latitude.between' => 'The latitude must be between -90 and 90.',
            'longitude.between' => 'The longitude must be between -180 and 180.',
            'gender.in' => 'The selected gender is invalid.',
            'user_role.exists' => 'The selected user role is invalid.',
            'provider_avatar.url' => 'The provider avatar must be a valid URL.',
        ];
    }

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
}