<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PropertyResource extends JsonResource
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
            'property_address' => $this->property_address,
            'property_state' => $this->property_state,
            'property_city' => $this->property_city,
            'property_postal_code' => $this->property_postal_code,
            'property_country' => $this->property_country,
            'customer_id' => $this->customers->isNotEmpty() ? (int) $this->customers->first()->id : null,
            'customers' => $this->customers->map(function ($customer) {
             return [
                'id' => (int) $customer->id,
                'name' => $customer->name . ' ' . $customer->last_name,
                'role' => $customer->pivot->role
            ];
            }),
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
