<?php

namespace App\Repositories;

use App\Models\CustomerSignature;
use App\Models\User;
use App\Interfaces\CustomerSignatureRepositoryInterface;

class CustomerSignatureRepository implements CustomerSignatureRepositoryInterface
{
    public function index()
    {
        return CustomerSignature::orderBy('id', 'DESC')->get();
    }

    public function getByUuid(string $uuid)
    {
        return CustomerSignature::where('uuid', $uuid)->firstOrFail();
    }

    public function store(array $data)
    {
        return CustomerSignature::create($data);
    }

    public function update(array $data, string $uuid)
    {
        $signature = CustomerSignature::where('uuid', $uuid)->firstOrFail();
        $signature->update($data);
        return $signature;
    }

    public function delete(string $uuid)
    {
        $signature = CustomerSignature::where('uuid', $uuid)->firstOrFail();
        $signature->delete();
        return $signature;
    }

    public function getAllSignatures()
    {
        return CustomerSignature::orderBy('id', 'DESC')->get();
    }

    public function getSignaturesByUserId(int $userId)
    {
        return CustomerSignature::where('user_id_ref_by', $userId)
            ->orderBy('id', 'DESC')
            ->get();
    }

    public function isSuperAdmin(User $user): bool
    {
        return $user->hasRole('Super Admin');
    }
}