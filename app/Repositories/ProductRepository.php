<?php

namespace App\Repositories;
use App\Models\Product;
use App\Interfaces\ProductRepositoryInterface;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function index(){
         return Product::withTrashed()->orderBy('id', 'DESC')->get();
       
    }

     public function getByUuid(string $uuid)
    {
        return Product::where('uuid', $uuid)->firstOrFail();
    }

    public function store(array $data){
       return Product::create($data);
    }

    public function update(array $data, $uuid)
{
    $product = $this->getByUuid($uuid);
    
    $product->update($data);

    return $product;
}
    
    public function delete(string $uuid)
    {
        $product = Product::where('uuid', $uuid)->firstOrFail();
        $product->delete();
        return $product;
    }

    public function restore($uuid)
    {
        $product = Product::withTrashed()->where('uuid', $uuid)->firstOrFail();
        if (!$product->trashed()) {
            throw new \Exception('Product already restored');
        }

        $product->restore();

        return $product;
    }
}
