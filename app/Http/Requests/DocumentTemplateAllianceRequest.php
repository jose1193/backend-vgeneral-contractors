<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class DocumentTemplateAllianceRequest extends FormRequest
{
    private const UNIQUE_TEMPLATE_TYPES = ['Agreement Alliance', 'Agreement Alliance Full'];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isStoreRoute = $this->is('api/document-template-alliance/store');
        $routeId = $this->route('id');
        $templateType = $this->input('template_type_alliance');

        return [
            'template_name_alliance' => [
                $isStoreRoute ? 'required' : 'sometimes',
                'string',
                'max:255',
                Rule::unique('document_template_alliances', 'template_name_alliance')
                    ->when(!$isStoreRoute, fn($rule) => $rule->ignore($routeId))
            ],
            'template_description_alliance' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'template_type_alliance' => [
                $isStoreRoute ? 'required' : 'sometimes',
                'string',
                'max:255',
                in_array($templateType, self::UNIQUE_TEMPLATE_TYPES) ? 
                    Rule::unique('document_template_alliances', 'template_type_alliance')
                        ->when(!$isStoreRoute, fn($rule) => $rule->ignore($routeId)) : 
                    null
            ],
            'template_path_alliance' => [
                $isStoreRoute ? 'required' : 'nullable',
                'file',
                'mimes:doc,docx,pdf',
                'max:10048'
            ],
            'alliance_company_id' => [
                $isStoreRoute ? 'required' : 'sometimes',
                'integer',
                'exists:alliance_companies,id'
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'template_name_alliance.required' => 'The alliance template name is required.',
            'template_name_alliance.string' => 'The alliance template name must be a text string.',
            'template_name_alliance.max' => 'The alliance template name cannot exceed :max characters.',
            'template_name_alliance.unique' => 'An alliance template with this name already exists.',

            'template_description_alliance.string' => 'The alliance template description must be a text string.',
            'template_description_alliance.max' => 'The alliance template description cannot exceed :max characters.',

            'template_type_alliance.required' => 'The alliance template type is required.',
            'template_type_alliance.string' => 'The alliance template type must be a text string.',
            'template_type_alliance.max' => 'The alliance template type cannot exceed :max characters.',
            'template_type_alliance.unique' => 'This type of alliance template already exists.',

            'template_path_alliance.required' => 'Please select an alliance template file.',
            'template_path_alliance.file' => 'The alliance template must be a valid file.',
            'template_path_alliance.mimes' => 'The alliance template must be a Word document or PDF.',
            'template_path_alliance.max' => 'The alliance template file size cannot exceed 10MB.',

            'alliance_company_id.required' => 'The alliance company is required.',
            'alliance_company_id.integer' => 'The alliance company ID must be a number.',
            'alliance_company_id.exists' => 'The selected alliance company is invalid.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        Log::warning('Document Template Alliance Request Validation Failed', [
            'errors' => $validator->errors()->toArray(),
            'input' => $this->all()
        ]);

        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422)
        );
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            // Add any additional custom validations if necessary
        });
    }
}