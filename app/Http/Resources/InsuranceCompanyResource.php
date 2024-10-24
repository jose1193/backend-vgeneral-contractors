<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InsuranceCompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Obtener los nombres de las alianzas prohibidas
        $prohibitedAllianceNames = $this->alliances->pluck('alliance_company_name')->implode(', '); 

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'insurance_company_name' => $this->insurance_company_name 
                                    . ($this->alliances->isNotEmpty() ? ' (N/A Alliance - ' . $prohibitedAllianceNames . ')' : ''),
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'created_by_user' => $this->user ? new UserResource($this->user) : null,
            
            'alliance_companies' => $this->alliances ? AllianceCompanyResource::collection($this->alliances) : null,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}