<?php

namespace App\Repositories;
use App\Models\InsuranceCompany;


use App\Interfaces\InsuranceCompanyRepositoryInterface;


class InsuranceCompanyRepository implements InsuranceCompanyRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function index(){
        return InsuranceCompany::orderBy('id', 'DESC')->get();
    }

     public function getByUuid(string $uuid)
    {
        return InsuranceCompany::where('uuid', $uuid)->firstOrFail();
    }

    public function store(array $data){
       return InsuranceCompany::create($data);
    }

    public function update(array $data, $uuid)
{
    $insuranceCompany = InsuranceCompany::where('uuid', $uuid)->firstOrFail();
    $insuranceCompany->update($data);
    return $insuranceCompany;
}

    
    public function delete(string $uuid)
    {
        $data = InsuranceCompany::where('uuid', $uuid)->firstOrFail();
        $data->delete();
        return $data;
    }

    
}
