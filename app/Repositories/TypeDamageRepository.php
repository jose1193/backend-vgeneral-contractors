<?php

namespace App\Repositories;
use App\Models\TypeDamage;
use App\Models\User;
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

    public function findByName(string $findName)
    {
    return TypeDamage::where('type_damage_name', $findName)->first(); 
    }


    public function isSuperAdmin(int $userId): bool
    {
    return User::findOrFail($userId)->hasRole('Super Admin');
    }

    public function store(array $data){
       return TypeDamage::create($data);
    }

    public function update(array $data, string $uuid) 
    {
        $typeDamage = $this->getByUuid($uuid);
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
