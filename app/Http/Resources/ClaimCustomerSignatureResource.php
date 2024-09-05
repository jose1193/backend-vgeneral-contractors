<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ClaimCustomerSignatureResource extends JsonResource
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
        
        // Obtener los datos del cliente relacionados
        'customer' => $this->customer ? [
            'id' => (int) $this->customer->id,
            'name' => $this->customer->name, // Ajusta según los campos de tu modelo Customer
            'email' => $this->customer->email, // Ajusta según los campos de tu modelo Customer
            // Añade aquí otros campos necesarios de Customer
        ] : null,

        // Obtener los datos del reclamo relacionado
        'claim' => $this->claim ? [
            'id' => (int) $this->claim->id,
            'title' => $this->claim->title, // Ajusta según los campos de tu modelo Claim
            'description' => $this->claim->description, // Ajusta según los campos de tu modelo Claim
            // Añade aquí otros campos necesarios de Claim
        ] : null,

        // Asegúrate de que `signature_data` sea la URL correcta para una firma
        'signature_data' => asset($this->signature_data),
       
        'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
        'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
    ];
}


}
