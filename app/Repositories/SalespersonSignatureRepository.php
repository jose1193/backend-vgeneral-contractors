<?php

namespace App\Repositories;

use App\Models\SalespersonSignature;

use App\Interfaces\SalespersonSignatureRepositoryInterface;

class SalespersonSignatureRepository implements SalespersonSignatureRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function index()
    {
        return SalespersonSignature::orderBy('id', 'DESC')->get();
    }

    
    public function getByUuid(string $uuid)
    {
        return SalespersonSignature::where('uuid', $uuid)->firstOrFail();
    }

     public function store(array $data)
    {
        return SalespersonSignature::create($data);
    }

    

    public function update(array $data, string $uuid)
    {
        $signature = SalespersonSignature::where('uuid', $uuid)->firstOrFail();
        $signature->update($data);
        return $signature;
    }

    public function delete(string $uuid)
    {
        $signature = SalespersonSignature::where('uuid', $uuid)->firstOrFail();
        $signature->delete();
        return $signature;
    }

    public function getSignaturesByUser($user)
    {
        if ($user->hasPermissionTo('Super Admin', 'api')) {
            // Si el usuario tiene el permiso de "Director Assistant", obtiene todas las firmas
            return SalespersonSignature::orderBy('id', 'DESC')->get();
        } else {
           
            return SalespersonSignature::where('seller_id', $user->id)
            ->orderBy('id', 'DESC')
            ->get();
        }
    }
}
