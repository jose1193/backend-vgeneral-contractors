<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ScopeSheetZoneResource extends JsonResource
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

            'zone_id' => (int) $this->zone_id,

            'zone' => $this->zone ? $this->zone->zone_name  : null,
            'zone_order' => (int) $this->zone_order,
            'zone_notes' => $this->zone_notes,
            'zone_photos' => ScopeSheetZonePhotoResource::collection($this->photos),
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
           

        ];
    }
}
