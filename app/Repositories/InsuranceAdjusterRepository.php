<?php

namespace App\Repositories;
use App\Models\InsuranceAdjuster;
use App\Interfaces\InsuranceAdjusterRepositoryInterface;


class InsuranceAdjusterRepository implements InsuranceAdjusterRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function index(){
        return InsuranceAdjuster::orderBy('id', 'DESC')->get();
    }

    public function getByUuid(string $uuid)
    {
        return InsuranceAdjuster::where('uuid', $uuid)->firstOrFail();
    }

    public function store(array $data)
    {
        return InsuranceAdjuster::create($data);
    }


    public function update(array $data, $uuid)
{
    $insurance_adjuster = $this->getByUuid($uuid);
    
    $insurance_adjuster->update($data);

    return $insurance_adjuster;
   }

   public function delete(string $uuid)
    {
        $insurance_adjuster = InsuranceAdjuster::where('uuid', $uuid)->firstOrFail();

        return $insurance_adjuster->delete();
    }

    public function getByUserIdAndCompanyIdExceptCurrent(int $userId, int $companyId, string $excludeUuid = null)
{
    $query = InsuranceAdjuster::where('insurance_company_id', $companyId)
        ->where('user_id', $userId);

    if ($excludeUuid) {
        $query->where('uuid', '!=', $excludeUuid);
    }

    return $query->first(); 
}

public function getByUserIdAndCompanyId(int $userId, int $companyId)
{
    return InsuranceAdjuster::where('insurance_company_id', $companyId)
        ->where('user_id', $userId)
        ->first();
}

}
