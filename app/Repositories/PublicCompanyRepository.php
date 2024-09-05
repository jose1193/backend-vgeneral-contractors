<?php

namespace App\Repositories;
use App\Models\PublicCompany;
use App\Interfaces\PublicCompanyRepositoryInterface;

class PublicCompanyRepository implements PublicCompanyRepositoryInterface
{
    
    /**
     * Create a new class instance.
     */
     public function index(){
        return PublicCompany::orderBy('id', 'DESC')->get();
    }

     public function getByUuid(string $uuid)
    {
        return PublicCompany::where('uuid', $uuid)->firstOrFail();
    }

    public function store(array $data){
       return PublicCompany::create($data);
    }

    public function update(array $data, $uuid)
{
    $dataCompany = PublicCompany::where('uuid', $uuid)->firstOrFail();
    $dataCompany->update($data);
    return $dataCompany;
}

    
    public function delete(string $uuid)
    {
        $data = PublicCompany::where('uuid', $uuid)->firstOrFail();
        $data->delete();
        return $data;
    }
}
