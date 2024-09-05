<?php

namespace App\Repositories;

use App\Models\CustomerSignature;

use App\Interfaces\CustomerSignatureRepositoryInterface;


class CustomerSignatureRepository implements CustomerSignatureRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function index()
    {
        return CustomerSignature::orderBy('id', 'DESC')->get();
    }

    
    public function getByUuid(string $uuid)
    {
        return CustomerSignature::where('uuid', $uuid)->firstOrFail();
    }

     public function store(array $data)
    {
        return CustomerSignature::create($data);
    }

     public function update(array $data, string $uuid)
{
    $claim = CustomerSignature::where('uuid', $uuid)->firstOrFail();
    $claim->update($data);
    return $claim;
}


    public function delete(string $uuid)
    {
        $claim = CustomerSignature::where('uuid', $uuid)->firstOrFail();
        $claim->delete();
        return $claim;
    }

    public function getSignaturesByUser($user)
    {
        if ($user->hasPermissionTo('Director Assistant', 'api')) {
            // Si el usuario tiene el permiso de "Director Assistant", obtiene todas las firmas
            return CustomerSignature::orderBy('id', 'DESC')->get();
        } else {
            // De lo contrario, obtiene solo las firmas asociadas a los reclamos del usuario
            return CustomerSignature::where('user_id_ref_by', $user->id)
            ->orderBy('id', 'DESC')
            ->get();
        }
    }
    


}
