<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClaimAgreementFullResource extends JsonResource
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
      'claim_id' => (int) $this->claim_id,
      'full_pdf_path' => asset($this->full_pdf_path),
      'agreement_type' => $this->agreement_type,
      'generated_by' => $this->generatedBy ? $this->generatedBy->name . ' ' . $this->generatedBy->last_name : null,
      'customers' => $this->claim->property->customers->map(function ($customer) {
                return [
                    'id' => (int) $customer->id,
                    'name' => $customer->name . ' ' . $customer->last_name, // Concatenar nombre y apellido
                    'role' => $customer->pivot->role,
                ];
            })->toArray(),
      'created_at' => $this->created_at->toDateTimeString(),
      'updated_at' => $this->updated_at->toDateTimeString(),
    ];
    }
}
