<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScopeSheetResource extends JsonResource
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
            'claim_id' => (int) $this->claim_id,
            'scope_sheet_description' => $this->scope_sheet_description,
            'generated_by' => $this->generatedBy ? $this->generatedBy->name . ' ' . $this->generatedBy->last_name : null,
            'presentations_images' => ScopeSheetPresentationResource::collection($this->presentations),
            'zones' => ScopeSheetZoneResource::collection($this->zones),
            'scope_sheet_export' => ScopeSheetExportResource::collection($this->scopeSheetExport),
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        
        ];


    }
}
