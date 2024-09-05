<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScopeSheetExportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'scope_sheet_id' => (int) $this->scope_sheet_id,
            'full_pdf_path' => $this->full_pdf_path,
            'generated_by' => $this->generatedBy ? $this->generatedBy->name . ' ' . $this->generatedBy->last_name : null,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        
        ];
    }
}
