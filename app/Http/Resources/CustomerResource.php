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
            'user_id' => (int) $this->user_id,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
            'deleted_at' => $this->deleted_at ? $this->deleted_at->toDateTimeString() : null,
        ];
    }
}
