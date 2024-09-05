<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FileEsxResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (int) $this->id,
            'uuid' => $this->uuid,
            'file_name' => $this->file_name,
            'file_path' => asset($this->file_path),
            'uploaded_by' => $this->uploader->name. ' '. $this->uploader->last_name,
            'assigned_to' => $this->assignedAdjusters->map(function ($adjuster) {
            return $adjuster->name . ' ' . $adjuster->last_name;
            })->toArray(),
            'user_id' => (int) $this->user_id,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
            
        ];
    }
}
