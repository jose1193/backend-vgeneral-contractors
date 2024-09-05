<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;


class ScopeSheetZonePhotoRequest extends FormRequest
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
    // Determina el tipo de operación basado en el método HTTP
    $isStore = $this->isMethod('post');
    $isUpdate = $this->isMethod('put') || $this->isMethod('patch');
    $isReorder = $this->isMethod('patch') && $this->routeIs('reorder');

    return [
        // Reglas para almacenar una nueva foto
        'scope_sheet_zone_id' => $isStore ? 'required|exists:scope_sheet_zones,id' : ($isReorder ? 'required|exists:scope_sheet_zones,id' : 'nullable|exists:scope_sheet_zones,id'),
        'photo_path' => $isStore ? 'required|array' : 'nullable',
        'photo_path.*' => $isStore ? [
            'required',
            'image',
            'mimes:jpg,jpeg,png',
            'max:15360',
        ] : [
            'sometimes',
            'image',
            'mimes:jpg,jpeg,png',
            'max:15360',
        ],

        // Reglas para reordenar fotos
        'ordered_photo_ids' => $isReorder ? 'required|array' : 'nullable',
        'ordered_photo_ids.*' => $isReorder ? 'integer|exists:scope_sheet_zone_photos,id' : 'nullable',
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
