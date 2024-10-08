<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class CauseOfLossRequest extends FormRequest
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
            'cause_loss_name' => $this->getCauseLossNameRules(),
            'description' => ['nullable', 'string', 'max:255'],
            'severity' => ['nullable', Rule::in(['low', 'medium', 'high'])],
        ];

        if ($this->isMethod('put') || $this->isMethod('patch')) {
            // Aplicar 'sometimes' para PUT o PATCH
            $rules = array_map(fn($rule) => array_merge(['sometimes'], (array)$rule), $rules);
        }

        return $rules;
    }

    /**
     * Get the validation rules for 'cause_loss_name' with uniqueness check for POST.
     */
    private function getCauseLossNameRules(): array
    {
        $rules = ['required', 'string', 'max:255'];

        if ($this->isMethod('post')) {
            $rules[] = Rule::unique('cause_of_losses', 'cause_loss_name');
        } elseif ($this->isMethod('put') || $this->isMethod('patch')) {
            $currentCauseOfLoss = $this->route('cause-of-loss');
        
            // Verificar si el nombre no ha cambiado
            if ($currentCauseOfLoss && $currentCauseOfLoss->cause_loss_name !== $this->input('cause_loss_name')) {
                $rules[] = Rule::unique('cause_of_losses', 'cause_loss_name')->ignore($currentCauseOfLoss->id);
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
            'cause_loss_name.unique' => 'This cause of loss name is already registered.',
            'severity.in' => 'The severity must be one of the following: low, medium, high.',
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
