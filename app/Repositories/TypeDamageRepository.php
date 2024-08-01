<?php

namespace App\Repositories;
use App\Models\TypeDamage;
use App\Interfaces\TypeDamageRepositoryInterface;

class TypeDamageRepository implements TypeDamageRepositoryInterface
{
    public function index(){
        return TypeDamage::orderBy('id', 'DESC')->get();
    }

     public function getByUuid(string $uuid)
    {
        return TypeDamage::where('uuid', $uuid)->firstOrFail();
    }

    public function store(array $data){
       return TypeDamage::create($data);
    }

    public function update(array $data, $uuid) 
    {
        $typeDamage = TypeDamage::where('uuid', $uuid)->firstOrFail();
        $typeDamage->update($data);
        return $typeDamage;
    }
    
    public function delete(string $uuid)
    {
        $typeDamage = TypeDamage::where('uuid', $uuid)->firstOrFail();
        $typeDamage->delete();
        return $typeDamage;
    }
}
