<?php

namespace App\Repositories;
use App\Models\Zone;
use App\Interfaces\ZoneRepositoryInterface;

class ZoneRepository implements ZoneRepositoryInterface
{
    /**
     * Create a new class instance.
     */
     public function index(){
         return Zone::withTrashed()->orderBy('id', 'DESC')->get();
       
    }

     public function getByUuid(string $uuid)
    {
        return Zone::where('uuid', $uuid)->firstOrFail();
    }

    public function store(array $data){
       return Zone::create($data);
    }

    public function update(array $data, $uuid)
{
    $zone = $this->getByUuid($uuid);
    
    $zone->update($data);

    return $zone;
}
    
    public function delete(string $uuid)
    {
        $zone = Zone::where('uuid', $uuid)->firstOrFail();
        $zone->delete();
        return $zone;
    }

    public function restore($uuid)
    {
        $zone = Zone::withTrashed()->where('uuid', $uuid)->firstOrFail();
        if (!$zone->trashed()) {
            throw new \Exception('Zone already restored');
        }

        $zone->restore();

        return $zone;
    }
}
