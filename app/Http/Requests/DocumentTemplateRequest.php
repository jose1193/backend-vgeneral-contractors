<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class DocumentTemplateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'template_name' => ['required', 'string', 'max:255'],
            'template_description' => ['nullable', 'string', 'max:255'],
            'template_type' => [
                'required',
                'string',
                'max:255',
                Rule::unique('document_templates', 'template_type')->ignore($this->route('id')),
            ],
            'template_path' => [
                'required',
                'file',
                'mimes:doc,docx',
                'max:10048',
            ],
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
            'template_type.unique' => 'This template type already exists.',
            'template_path.file' => 'The :attribute must be a file.',
            'template_path.mimes' => 'The :attribute must be a file of type: doc, docx.',
            'template_path.max' => 'The :attribute may not be greater than :max kilobytes.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        // Log the validation errors
        Log::warning('Document Template Request Validation Failed', [
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
}