<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class TypeDamageRequest extends FormRequest
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
        $rules = [
            'type_damage_name' => $this->getTypeDamageNameRules(),
            'description' => ['nullable', 'string', 'max:255'],
            'severity' => ['nullable', 'integer'],
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            // Aplicar 'sometimes' para PUT o PATCH
            $rules = array_map(fn($rule) => array_merge(['sometimes'], (array)$rule), $rules);
        }

        return $rules;
    }

    /**
     * Get the validation rules for 'type_damage_name' with uniqueness check for POST.
     */
    private function getTypeDamageNameRules(): array
    {
        $rules = ['required', 'string', 'max:255'];

        if ($this->isMethod('post')) {
        $rules[] = Rule::unique('type_damages', 'type_damage_name');
        } elseif ($this->isMethod('put') || $this->isMethod('patch')) {
        $currentTypeDamage = $this->route('type-damage');
        
            // Verificar si el nombre no ha cambiado
            if ($currentTypeDamage && $currentTypeDamage->type_damage_name !== $this->input('type_damage_name')) {
            $rules[] = Rule::unique('type_damages', 'type_damage_name')->ignore($currentTypeDamage->id);
            }
        }

        return $rules;
    }

    /**
     * Get custom error messages for the request.
     */
    public function messages(): array
    {
        return [
            'required' => 'The :attribute field is required.',
            'string' => 'The :attribute must be a string.',
            'max' => 'The :attribute may not be greater than :max characters.',
            'type_damage_name.unique' => 'This type of damage name is already registered.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     */
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
