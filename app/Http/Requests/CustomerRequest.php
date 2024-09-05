<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerRequest extends FormRequest
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

    //\Log::info("Is update request: " . ($isUpdateRequest ? 'Yes' : 'No'));
    //\Log::info("Email being validated: " . $this->input('email'));

    return [
        'name' => ['required', 'string', 'max:40', 'regex:/^[a-zA-Z\s]+$/'],
        'last_name' => ['nullable', 'string', 'max:40', 'regex:/^[a-zA-Z\s]+$/'],
        
        'email' => [
            'required',
            'string',
            'email',
            'min:10',
            'max:255',
            function ($attribute, $value, $fail) use ($isUpdateRequest) {
                if ($isUpdateRequest) {
                    // Actualización: Verifica si el correo electrónico ya está registrado por otro cliente
                    $customerId = $this->route('uuid') ? Customer::where('uuid', $this->route('uuid'))->value('id') : null;

                    if ($customerId) {
                        $existingCustomer = Customer::where('email', $value)
                            ->where('id', '!=', $customerId)
                            ->exists();

                        if ($existingCustomer) {
                            $fail('The email has already been registered by another client.');
                        }
                    } else {
                        $fail('Could not find client to update.');
                    }
                } else {
                    // Registro: Verifica si el correo electrónico ya está registrado
                    if (Customer::where('email', $value)->exists()) {
                        $fail('The email has already been registered.');
                    }
                }
            },
        ],
        'cell_phone' => ['nullable', 'string', 'min:4', 'max:20'],
        'home_phone' => ['nullable', 'string', 'min:4', 'max:20'],
        'occupation' => ['nullable', 'string', 'max:40', 'regex:/^[a-zA-Z\s]+$/'],
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
