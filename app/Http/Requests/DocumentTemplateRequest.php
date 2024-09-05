<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;


class DocumentTemplateRequest extends FormRequest
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
    $isStoreRoute = $this->is('api/document-template/store');

    return [
        'template_name' => ($isStoreRoute ? 'required' : 'sometimes') . '|string|max:255',
        'template_description' => 'nullable|string|max:255',
        'template_type' => [
            $isStoreRoute ? 'required' : 'sometimes',
            'string',
            'max:255',
            // Aquí validamos que el template_type sea único en la tabla
            'unique:document_templates,template_type,' . $this->route('id') // Excluye el registro actual en actualización
        ],
       
        'template_path' => [
            $isStoreRoute ? 'required' : 'sometimes',
            'file',
            'mimes:doc,docx', // Asegura que el archivo sea un documento de Word
            'max:10048', // Tamaño máximo en kilobytes (10 MB)
        ],
    ];
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
