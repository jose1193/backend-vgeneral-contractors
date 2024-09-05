<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentTemplateAllianceResource extends JsonResource
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
            'template_name_alliance' => $this->template_name_alliance,
            'template_description_alliance' => $this->template_description_alliance,
            'template_type_alliance' => $this->template_type_alliance,
            'template_path_alliance' => asset($this->template_path_alliance),
            'alliance_company_id' => $this->allianceCompany->alliance_company_name, 
            'uploaded_by' =>  $this->uploadedBy->name . ' ' . $this->uploadedBy->last_name,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
