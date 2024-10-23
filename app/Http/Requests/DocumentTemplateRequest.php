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
    /**
     * Tipos de template que deben ser únicos
     */
    private const UNIQUE_TEMPLATE_TYPES = ['Agreement', 'Agreement Full'];

    /**
     * Determinar si el usuario está autorizado para realizar esta solicitud
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Obtener las reglas de validación que se aplican a la solicitud
     */
    public function rules(): array
    {
        $templateType = $this->input('template_type');
        $templateTypeRules = ['required', 'string', 'max:255'];

        // Solo aplicar reglas unique en POST
        if ($this->isMethod('post')) {
            // Solo aplicar la regla unique si el template_type es Agreement o Agreement Full
            if (in_array($templateType, self::UNIQUE_TEMPLATE_TYPES)) {
                $templateTypeRules[] = Rule::unique('document_templates', 'template_type');
            }
        }

        $baseRules = [
            'template_name' => [
                'required',
                'string',
                'max:255',
            ],
            'template_description' => [
                'nullable',
                'string',
                'max:1000'
            ],
            'template_type' => $templateTypeRules,
            'template_path' => [
                'required',
                'file',
                'mimes:doc,docx,pdf',
                'max:10048', // 10MB en kilobytes
            ],
            'signature_path_id' => [
                'nullable',
                'exists:company_signatures,id'
            ],
            'uploaded_by' => [
                'nullable',
                'exists:users,id'
            ],
        ];

        // Agregar reglas unique solo para POST
        if ($this->isMethod('post')) {
            $baseRules['template_name'][] = Rule::unique('document_templates', 'template_name');
        }

        // Modificar las reglas para solicitudes PUT/PATCH
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $baseRules = array_map(function ($rule) {
                return array_merge(['sometimes'], (array)$rule);
            }, $baseRules);

            // Hacer el template_path opcional en actualizaciones
            $baseRules['template_path'] = [
                'sometimes',
                'nullable',
                'file',
                'mimes:doc,docx,pdf',
                'max:10048'
            ];
        }

        return $baseRules;
    }

    /**
     * Obtener los mensajes de error personalizados para las reglas de validación.
     */
    public function messages(): array
    {
        return [
            // Mensajes para template_name
            'template_name.required' => 'The template name is required.',
            'template_name.string' => 'The template name must be a text string.',
            'template_name.max' => 'The template name cannot exceed :max characters.',
            'template_name.unique' => 'A template with this name already exists.',

            // Mensajes para template_description
            'template_description.string' => 'The template description must be a text string.',
            'template_description.max' => 'The template description cannot exceed :max characters.',

            // Mensajes para template_type
            'template_type.required' => 'The template type is required.',
            'template_type.string' => 'The template type must be a text string.',
            'template_type.max' => 'The template type cannot exceed :max characters.',
            'template_type.unique' => 'This type of agreement template already exists.',

            // Mensajes para template_path
            'template_path.required' => 'Please select a template file.',
            'template_path.file' => 'The template must be a valid file.',
            'template_path.mimes' => 'The template must be a Word document (doc or docx).',
            'template_path.max' => 'The template file size cannot exceed 10MB.',

            // Mensajes para signature_path_id
            'signature_path_id.exists' => 'The selected company signature is invalid.',

            // Mensajes para uploaded_by
            'uploaded_by.exists' => 'The selected user is invalid.',

            // Mensajes para fechas
            'created_at.date' => 'The creation date must be a valid date.',
            'updated_at.date' => 'The update date must be a valid date.',
        ];
    }

    /**
     * Manejar un intento de validación fallido.
     */
    protected function failedValidation(Validator $validator): void
    {
        // Registrar los errores de validación
        Log::warning('Document Template Request Validation Failed', [
            'errors' => $validator->errors()->toArray(),
            'input' => $this->all()
        ]);

        // Lanzar excepción con respuesta JSON
        throw new HttpResponseException(
            response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422)
        );
    }

    /**
     * Configurar el validador para que use reglas personalizadas si es necesario.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            // Aquí puedes agregar validaciones personalizadas adicionales si son necesarias
        });
    }
}