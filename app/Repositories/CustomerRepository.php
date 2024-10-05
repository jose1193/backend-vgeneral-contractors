<?php

namespace App\Repositories;

use App\Models\Customer;
use App\Models\CustomerSignature;
use App\Models\User;
use App\Interfaces\CustomerRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class CustomerRepository implements CustomerRepositoryInterface
{
    public function index(): Collection
    {
        return Customer::withTrashed()->orderBy('id', 'DESC')->get();
    }

    public function getByUuid(string $uuid): Customer
    {
        return Customer::where('uuid', $uuid)->firstOrFail();
    }

    public function getByUser($user)
    {
    if ($user->hasRole('Super Admin', 'api')) {
        return Customer::withTrashed()->orderBy('id', 'DESC')->get();
    } else {
        return Customer::where('user_id', $user->id)
                       ->orderBy('id', 'DESC')
                       ->get();
    }
    }

    public function getAllSuperAdmins(): Collection
    {
        return User::whereHas('roles', function ($query) {
            $query->where('name', 'Super Admin');
        })->get();
    }
    
    public function store(array $data): Customer
    {
        return Customer::create($data);
    }

    public function update(array $data, string $uuid): Customer
    {
        $customer = $this->getByUuid($uuid);
        $customer->update($data);
        return $customer;
    }

    public function delete(string $uuid): bool
    {
        $customer = $this->getByUuid($uuid);
        return $customer->delete();
    }

    public function restore(string $uuid): Customer
    {
        $customer = Customer::withTrashed()->where('uuid', $uuid)->firstOrFail();
        if (!$customer->trashed()) {
        throw new \Exception('Customer already restored');
    }

        $customer->restore();
        return $customer;
    }



    public function isSuperAdmin(int $userId): bool
    {
    return User::findOrFail($userId)->hasRole('Super Admin');
    }

    public function emailExistsForOtherCustomer(string $email, ?string $excludeUuid = null): bool
    {
        $query = Customer::where('email', $email);
        
        if ($excludeUuid) {
            $query->where('uuid', '!=', $excludeUuid);
        }

        return $query->exists();
    }

    public function emailExists(string $email): bool
    {
        return Customer::where('email', $email)->exists();
    }

    
}