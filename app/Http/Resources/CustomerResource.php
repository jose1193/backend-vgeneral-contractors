<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'name' => $this->name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'home_phone' => $this->home_phone,
            'cell_phone' => $this->cell_phone,
            'occupation' => $this->occupation,
            'property' => PropertyResource::collection($this->properties),
             // Obtener las firmas del cliente
            'signature_customer' => $this->customerSignature ? [
            'uuid' => $this->customerSignature->uuid,
            'customer_id' => $this->customerSignature->customer_id,
            'signature_data' => asset($this->customerSignature->signature_data),
            'created_at' => $this->customerSignature->created_at,
            'updated_at' => $this->customerSignature->updated_at,
            'created_by_user' => [
            'id' => $this->customerSignature->createdByUser->id,
            'name' => $this->customerSignature->createdByUser->name,
            'last_name' => $this->customerSignature->createdByUser->last_name,
            ],
            ] : null,
            'user_id' => (int) $this->user_id,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
            'deleted_at' => $this->deleted_at ? $this->deleted_at->toDateTimeString() : null,
        ];
    }
}
