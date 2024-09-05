<?php

namespace App\Repositories;
use App\Models\Customer;
use App\Interfaces\CustomerRepositoryInterface;

class CustomerRepository implements CustomerRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function index()
    {
        return Customer::withTrashed()->orderBy('id', 'DESC')->get();
    }

    /**
     * Find a customer by UUID.
     *
     * @param  string  $uuid
     * @return \App\Models\Customer
     */
    public function getByUuid($uuid)
    {
        return Customer::where('uuid', $uuid)->firstOrFail();
    }

    /**
     * Create a new customer.
     *
     * @param  array  $data
     * @return \App\Models\Customer
     */
    public function store(array $data)
    {
        return Customer::create($data);
    }

    /**
     * Update a customer by UUID.
     *
     * @param  array   $data
     * @param  string  $uuid
     * @return \App\Models\Customer
     */
    public function update(array $data, $uuid)
{
    $customer = $this->getByUuid($uuid);
    
    if (!$customer) {
        throw new ModelNotFoundException("Customer not found with UUID: {$uuid}");
    }
    
    $customer->update($data);

    return $customer;
}

    /**
     * Delete a customer by UUID.
     *
     * @param  string  $uuid
     * @return bool|null
     */
    public function delete($uuid)
    {
        $customer = Customer::where('uuid', $uuid)->firstOrFail();

        return $customer->delete();
    }

    /**
     * Restore a customer by UUID.
     *
     * @param  string  $uuid
     * @return \App\Models\Customer
     */
    public function restore($uuid)
    {
        $customer = Customer::withTrashed()->where('uuid', $uuid)->firstOrFail();
        if (!$customer->trashed()) {
            throw new \Exception('Customer already restored');
        }

        $customer->restore();

        return $customer;
    }
}
