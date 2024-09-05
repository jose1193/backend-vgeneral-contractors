<?php

namespace App\Repositories;
use App\Models\PublicAdjuster;
use App\Interfaces\PublicAdjusterRepositoryInterface;


class PublicAdjusterRepository implements PublicAdjusterRepositoryInterface
{
    /**
     * Create a new class instance.
     */
     public function index(){
        return PublicAdjuster::orderBy('id', 'DESC')->get();
    }

    public function getByUuid(string $uuid)
    {
        return PublicAdjuster::where('uuid', $uuid)->firstOrFail();
    }

    public function store(array $data)
    {
        return PublicAdjuster::create($data);
    }


    public function update(array $data, $uuid)
{
    $public_adjuster = $this->getByUuid($uuid);
    
    $public_adjuster->update($data);

    return $public_adjuster;
   }

   public function delete(string $uuid)
    {
        $public_adjuster = PublicAdjuster::where('uuid', $uuid)->firstOrFail();

        return $public_adjuster->delete();
    }

  public function getByUserIdAndCompanyIdExceptCurrent(int $userId, int $companyId, string $excludeUuid = null)
{
    $query = PublicAdjuster::where('public_company_id', $companyId)
        ->where('user_id', $userId);

    if ($excludeUuid) {
        $query->where('uuid', '!=', $excludeUuid);
    }

    return $query->first(); 
}

public function getByUserIdAndCompanyId(int $userId, int $companyId)
{
    return PublicAdjuster::where('public_company_id', $companyId)
        ->where('user_id', $userId)
        ->first();
}
}
