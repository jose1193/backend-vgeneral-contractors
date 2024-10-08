<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Route;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Validation\ValidationException;

class CreateUserRequest extends FormRequest
{
    public function __construct(
        private Guard $auth
    ) {}

    public function authorize(): bool
    {
        // Implement actual authorization logic here
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $isRegisterRoute = Route::is('api.register');
        $userId = $this->auth->id();

        return [
            'name' => ['required', 'string', 'max:40', 'regex:/^[a-zA-Z\s]+$/'],
            'last_name' => ['nullable', 'string', 'max:40', 'regex:/^[a-zA-Z\s]+$/'],
            'username' => [
                'required',
                'string',
                'max:30',
                'regex:/^[a-zA-Z0-9_]+$/',
                $isRegisterRoute
                    ? Rule::unique('users')
                    : Rule::unique('users')->ignore($userId),
            ],
            'register_date' => ['nullable', 'date'],
            'email' => $this->emailRules($isRegisterRoute, $userId),
            'password' => ['nullable', 'string', Password::min(5)->mixedCase()->numbers()->symbols()->uncompromised()],
            'generate_password' => ['nullable', 'boolean'],
            'phone' => ['nullable', 'string', 'min:4', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'address_2' => ['nullable', 'string', 'max:255'],
            'zip_code' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'gender' => ['nullable', Rule::in(['male', 'female', 'other'])],
            'user_role' => [$isRegisterRoute ? 'required' : 'nullable', 'exists:roles,id'],
            'provider' => ['nullable', 'string', 'min:4', 'max:20'],
            'provider_id' => ['nullable', 'string', 'min:4', 'max:30'],
            'provider_avatar' => ['nullable', 'url', 'max:255'],
        ];
    }

    private function emailRules(bool $isRegisterRoute, ?int $userId): array
    {
        $rules = [
            'required',
            'string',
            'email',
            'min:10',
            'max:255',
        ];

        $rules[] = $isRegisterRoute
            ? Rule::unique('users')
            : Rule::unique('users')->ignore($userId);

        return $rules;
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw ValidationException::withMessages($validator->errors()->toArray());
    }
}