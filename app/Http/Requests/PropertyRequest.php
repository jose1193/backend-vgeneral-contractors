<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use App\Models\Property;
use Illuminate\Http\Request;

class PropertyRequest extends FormRequest
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
        $isStoreRoute = $this->is('api/property/store');

        return [
            'property_address' => $isStoreRoute ? 'required|string|max:255' : 'nullable|string|max:255',
            'property_address_2' => 'nullable|string|max:255',
            'property_state' => 'nullable|string|max:255',
            'property_city' => 'nullable|string|max:255',
            'property_postal_code' => 'nullable|string|max:255',
            'property_country' => 'nullable|string|max:255',
            'property_latitude' => 'nullable|numeric|between:-90,90',
            'property_longitude' => 'nullable|numeric|between:-180,180',
            'property_country' => 'nullable|string|max:255',
            'customer_id' => $isStoreRoute
                ? ['required', 'array', 'min:1', 'max:2']
                : ['nullable', 'array', 'max:2'],
            'customer_id.*' => 'integer|exists:customers,id',
        ];
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
            'integer' => 'The :attribute must be an integer.',
            'exists' => 'The selected :attribute is invalid.',
            'array' => 'The :attribute must be an array.',
            'min' => 'The :attribute must have at least :min items.',
            'customer_id.required' => 'At least one customer must be associated with the property.',
            'customer_id.max' => 'No more than two customers can be associated with a property.',
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        // Log the validation errors
        Log::warning('Property Request Validation Failed', [
            'errors' => $validator->errors()->toArray(),
            'input' => $this->all()
        ]);

        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'errors'    => $validator->errors()
        ], 422));
    }
}