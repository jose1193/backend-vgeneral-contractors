<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SalespersonSignatureResource extends JsonResource
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
        'salesperson_id' => (int) $this->salesperson_id,
       
        'salesPerson' => $this->salesPerson ? [
            'id' => (int) $this->salesPerson->id,
            'name' => $this->salesPerson->name, 
            'last_name' => $this->salesPerson->last_name,
            'email' => $this->salesPerson->email, 
           
        ] : null,
         
        'registeredBy' => $this->registeredBy ? [
            'id' => (int) $this->registeredBy->id,
            'name' => $this->registeredBy->name, 
            'last_name' => $this->registeredBy->last_name,
            'email' => $this->registeredBy->email, 
            
        ] : null,

     
        'signature_path' => asset($this->signature_path),
       
        'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
        'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
        ];
    }
}
