<?php

namespace App\Repositories;

use App\Models\Claim;

use App\Models\AffidavitForm;
use App\Models\CompanySignature;


use App\Interfaces\ClaimRepositoryInterface;

class ClaimRepository implements ClaimRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function index()
    {
        return Claim::with('property.customers')->orderBy('id', 'DESC')->get();
    }

    public function getByUuid(string $uuid)
    {
        return Claim::with('property.customers')->where('uuid', $uuid)->firstOrFail();
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
        if ($user->hasPermissionTo('Manager', 'api')) {
            // Si el usuario tiene el permiso de "Lead", obtiene todos los claims
            return Claim::orderBy('id', 'DESC')->get();
        } else {
            // De lo contrario, obtiene solo los claims asociados a su id
            return Claim::withTrashed()->where('user_id_ref_by', $user->id)
                        ->orderBy('id', 'DESC')
                        ->get();
        }
    }
    
    public function storeAffidavitForm(array $affidavitDetails, int $claimId)
    {
    $affidavitDetails['claim_id'] = $claimId;
    AffidavitForm::create($affidavitDetails);
    }

    public function updateAffidavitForm(array $affidavitDetails, int $claimId)
    {
    $affidavit = AffidavitForm::where('claim_id', $claimId)->firstOrFail();
    $affidavit->update($affidavitDetails);
    }

    public function restore($uuid)
        {
        
        
        $claim = Claim::withTrashed()->where('uuid', $uuid)->firstOrFail();
        if (!$claim->trashed()) {
            throw new \Exception('Claim already restored');
        }

        $claim->restore();

        return $claim;
        }

        public function getSignaturePathId()
    {
        
        $companySignature = CompanySignature::latest()->first(); // O ajustar la lógica de obtención
        if ($companySignature) {
            return $companySignature->id;
        }

        
    }
}
