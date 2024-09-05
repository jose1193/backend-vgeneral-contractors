<?php

namespace App\Repositories;
use App\Models\ServiceRequest;
use App\Interfaces\ServiceRequestRepositoryInterface;


class ServiceRequestRepository implements ServiceRequestRepositoryInterface
{
    /**
     * Create a new class instance.
     */
     /**
     * Create a new class instance.
     */
    public function index(){
         return ServiceRequest::orderBy('id', 'DESC')->get();
       
    }

     public function getByUuid(string $uuid)
    {
        return ServiceRequest::where('uuid', $uuid)->firstOrFail();
    }

    public function store(array $data){
       return ServiceRequest::create($data);
    }

    public function update(array $data, $uuid)
{
    $service = $this->getByUuid($uuid);
    
    $service->update($data);

    return $service;
}
    
    public function delete(string $uuid)
    {
        $service = ServiceRequest::where('uuid', $uuid)->firstOrFail();
        $service->delete();
        return $service;
    }
}
