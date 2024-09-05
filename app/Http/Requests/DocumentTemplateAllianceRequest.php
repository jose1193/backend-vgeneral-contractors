<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;


class DocumentTemplateAllianceRequest extends FormRequest
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
    $isStoreRoute = $this->is('api/document-template-alliance/store');
    $routeId = $this->route('id');

     return [
        'template_name_alliance' => ($isStoreRoute ? 'required|' : 'sometimes|') . 'string|max:255',
        'template_description_alliance' => 'nullable|string|max:255',
        'template_type_alliance' => [
            $isStoreRoute ? 'required' : 'sometimes',
            'string',
            'max:255',
            'unique:document_template_alliances,template_type_alliance,' . $routeId, // Excludes the current record in update
        ],
        'template_path_alliance' => [
            $isStoreRoute ? 'required' : 'sometimes',
            'file',
            'mimes:doc,docx', // Ensures the file is a Word document
            'max:10048', // Maximum size in kilobytes (10 MB)
        ],
        'alliance_company_id' => [
        $isStoreRoute ? 'required' : 'sometimes',
        'integer',
        'exists:alliance_companies,id',
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
