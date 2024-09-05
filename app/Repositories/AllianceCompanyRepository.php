<?php

namespace App\Repositories;
use App\Models\AllianceCompany;


use App\Interfaces\AllianceCompanyRepositoryInterface;
class AllianceCompanyRepository implements AllianceCompanyRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function index(){
        return AllianceCompany::orderBy('id', 'DESC')->get();
    }

     public function getByUuid(string $uuid)
    {
        return AllianceCompany::where('uuid', $uuid)->firstOrFail();
    }

    public function store(array $data){
       return AllianceCompany::create($data);
    }

    public function update(array $data, $uuid)
{
    $company = AllianceCompany::where('uuid', $uuid)->firstOrFail();
    $company->update($data);
    return $company;
}

    
    public function delete(string $uuid)
    {
        $data = AllianceCompany::where('uuid', $uuid)->firstOrFail();
        $data->delete();
        return $data;
    }

}
