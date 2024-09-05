<?php

namespace App\Repositories;
use App\Models\CompanySignature;


use App\Interfaces\CompanySignatureRepositoryInterface;

class CompanySignatureRepository implements CompanySignatureRepositoryInterface
 {
    /**
     * Create a new class instance.
     */
    public function index(){
        return CompanySignature::orderBy('id', 'DESC')->get();
    }

     public function getByUuid(string $uuid)
    {
        return CompanySignature::where('uuid', $uuid)->firstOrFail();
    }

    public function store(array $data){
       return CompanySignature::create($data);
    }

    public function update(array $data, $uuid)
{
    $company_signature = CompanySignature::where('uuid', $uuid)->firstOrFail();
    $company_signature->update($data);
    return $company_signature;
}

    
    public function delete(string $uuid)
    {
        $data = CompanySignature::where('uuid', $uuid)->firstOrFail();
        $data->delete();
        return $data;
    }

    public function findByUserId(int $userId)
    {
    return CompanySignature::where('user_id', $userId)->first();
    }

 public function findFirst()
    {
        return CompanySignature::first();
    }

}
