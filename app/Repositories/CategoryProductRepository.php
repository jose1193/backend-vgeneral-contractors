<?php

namespace App\Repositories;
use App\Models\CategoryProduct;
use App\Interfaces\CategoryProductRepositoryInterface;

class CategoryProductRepository implements CategoryProductRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function index(){
        return CategoryProduct::orderBy('id', 'DESC')->get();
    }

    public function getByUuid(string $uuid)
    {
        return CategoryProduct::where('uuid', $uuid)->firstOrFail();
    }

     public function store(array $data)
    {
        return CategoryProduct::create($data);
    }


     public function update(array $data, $uuid)
{
    $product_category = $this->getByUuid($uuid);
    
    $product_category->update($data);

    return $product_category;
   }

   public function delete($uuid)
    {
        $product_category = CategoryProduct::where('uuid', $uuid)->firstOrFail();

        return $product_category->delete();
    }
}
