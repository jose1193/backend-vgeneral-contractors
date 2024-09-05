<?php

namespace App\Repositories;

use App\Models\ClaimCustomerSignature;
use App\Models\Claim;


use App\Interfaces\ClaimCustomerSignatureRepositoryInterface;

class ClaimCustomerSignatureRepository implements ClaimCustomerSignatureRepositoryInterface
{
    public function index()
    {
        return ClaimCustomerSignature::orderBy('id', 'DESC')->get();
    }

     public function getClaimByUuid(string $uuid)
    {
        return Claim::where('uuid', $uuid)->firstOrFail();
    }

 
    public function getByUuid(string $uuid)
    {
        return ClaimCustomerSignature::where('uuid', $uuid)->firstOrFail();
    }

     public function store(array $data)
    {
        return ClaimCustomerSignature::create($data);
    }

    public function update(array $data, string $uuid)
{
    $claim = ClaimCustomerSignature::where('uuid', $uuid)->firstOrFail();
    $claim->update($data);
    return $claim;
}


    public function delete(string $uuid)
    {
        $claim = ClaimCustomerSignature::where('uuid', $uuid)->firstOrFail();
        $claim->delete();
        return $claim;
    }

    public function getSignaturesByUser($user)
    {
        if ($user->hasPermissionTo('Director Assistant', 'api')) {
            // Si el usuario tiene el permiso de "Director Assistant", obtiene todas las firmas
            return ClaimCustomerSignature::orderBy('id', 'DESC')->get();
        } else {
            // De lo contrario, obtiene solo las firmas asociadas a los reclamos del usuario
            return ClaimCustomerSignature::whereIn('claim_id', function($query) use ($user) {
                $query->select('id')
                      ->from('claims')
                      ->where('user_id_ref_by', $user->id);
            })
            ->orderBy('id', 'DESC')
            ->get();
        }
    }
    
    public function findExistingSignature(int $claimId, int $customerId)
{
    return ClaimCustomerSignature::where('claim_id', $claimId)
                                 ->where('customer_id', $customerId)
                                 ->first();
}


}
