<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

use Illuminate\Validation\Rules\Password;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Route;

class CreateUserRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
   public function rules(): array
{
    // Verifica si la ruta actual es para registro
    $isRegisterRoute = Route::currentRouteName() === 'api/register';

    // Obtén el ID del usuario actual (si existe)
    $userId = auth()->id();

    $rules = [
        'name' => ['required', 'string', 'max:40', 'regex:/^[a-zA-Z\s]+$/'],
        'last_name' => ['nullable', 'string', 'max:40', 'regex:/^[a-zA-Z\s]+$/'],
        'username' => [
            'required',
            'string',
            'max:30',
            'regex:/^[a-zA-Z0-9_]+$/',
        ],
        'register_date' => ['nullable', 'string', 'max:255'],
        'email' => [
            'required',
            'string',
            'email',
            'min:10',
            'max:255',
        ],
        'password' => [
            $isRegisterRoute ? 'required' : 'nullable',
            'string',
            Password::min(5)->mixedCase()->numbers()->symbols()->uncompromised(),
        ],
        'phone' => ['nullable', 'string', 'min:4', 'max:20'],
        'address' => ['nullable', 'string', 'max:255'],
        'zip_code' => ['nullable', 'string', 'max:20'],
        'city' => ['nullable', 'string', 'max:255'],
        'country' => ['nullable', 'string', 'max:255'],
        'gender' => ['nullable', 'in:male,female,other'],
        'user_role' => [$isRegisterRoute ? 'required' : 'nullable', 'exists:roles,id'],
        'provider' => ['nullable', 'min:4', 'max:20'],
        'provider_id' => ['nullable', 'min:4', 'max:30'],
        'provider_avatar' => ['nullable', 'min:4', 'max:255'],
    ];

    // Aplica la regla 'unique' de manera condicional
    if ($isRegisterRoute) {
        // Para registro, username y email deben ser únicos
        $rules['username'][] = 'unique:users';
        $rules['email'][] = 'unique:users';
    } else {
        // Para actualización, ignora el usuario actual
        $rules['username'][] = Rule::unique('users')->ignore($userId);
        $rules['email'][] = Rule::unique('users')->ignore($userId);
    }

    return $rules;
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
