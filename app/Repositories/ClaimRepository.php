<?php

namespace App\Repositories;

use App\Models\Claim;


use App\Interfaces\ClaimRepositoryInterface;

class ClaimRepository implements ClaimRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function index()
    {
        return Claim::orderBy('id', 'DESC')->get();
    }

    public function getByUuid(string $uuid)
    {
        return Claim::where('uuid', $uuid)->firstOrFail();
    }

    public function store(array $data)
    {
        return Claim::create($data);
    }

    public function update(array $data, string $uuid)
    {
        $claim = Claim::where('uuid', $uuid)->firstOrFail();
        $claim->update($data);
        return $claim;
    }

    public function delete(string $uuid)
    {
        $claim = Claim::where('uuid', $uuid)->firstOrFail();
        $claim->delete();
        return $claim;
    }

    public function getClaimsByUser($user)
    {
        if ($user->hasPermissionTo('Director Assistant', 'api')) {
            // Si el usuario tiene el permiso de "Director Assistant", obtiene todos los claims
            return Claim::orderBy('id', 'DESC')->get();
        } else {
            // De lo contrario, obtiene solo los claims asociados a su id
            return Claim::where('user_id_ref_by', $user->id)
                        ->orderBy('id', 'DESC')
                        ->get();
        }
    }
    
   
}