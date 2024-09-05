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
        'property_state' => 'nullable|string|max:255',
        'property_city' => 'nullable|string|max:255',
        'property_postal_code' => 'nullable|string|max:255',
        'property_country' => 'nullable|string|max:255',
        'customer_id' => $isStoreRoute 
            ? ['required', 'array', 'min:1', 'max:2'] 
            : ['nullable', 'array', 'max:2'],
        'customer_id.*' => 'integer|exists:customers,id',
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
