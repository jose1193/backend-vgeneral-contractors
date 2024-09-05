<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
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
            'product_category_id' => $this->product_category_id,
            'product_category' => CategoryProductResource::collection($this->categoryProduct), 
            
            'product_name' => $this->product_name,
            'product_description' => $this->product_description,
            'price' => (double) $this->price,
            'unit' =>  $this->id,
            'created_at' => $this->created_at ? $this->created_at->toDateTimeString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toDateTimeString() : null,
            'deleted_at' => $this->deleted_at ? $this->deleted_at->toDateTimeString() : null,
        ];
    }
}
