<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentTemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'template_name' => $this->template_name,
            'template_description' => $this->template_description,
            'template_type' => $this->template_type,
            'template_path' => asset($this->template_path),
            'uploaded_by' =>  $this->upload_by->name . ' ' . $this->upload_by->last_name,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
