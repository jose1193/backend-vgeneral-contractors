<?php

namespace App\Repositories;

use App\Models\SalespersonSignature;
use App\Models\User;
use App\Interfaces\SalespersonSignatureRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class SalespersonSignatureRepository implements SalespersonSignatureRepositoryInterface
{
    public function index(): Collection
    {
        return SalespersonSignature::orderBy('id', 'DESC')->get();
    }

    public function getByUuid(string $uuid): ?SalespersonSignature
    {
        return SalespersonSignature::where('uuid', $uuid)->firstOrFail();
    }

    public function getSignaturesByUser(object $user): Collection
    {
        if ($user->hasPermissionTo('Super Admin', 'api')) {
            return SalespersonSignature::orderBy('id', 'DESC')->get();
        } else {
            return SalespersonSignature::where('salesperson_id', $user->id)
                ->orderBy('id', 'DESC')
                ->get();
        }
    }

    public function getBySalespersonId(int $salespersonId)
    {
        return SalespersonSignature::where('salesperson_id', $salespersonId)
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function store(array $data): SalespersonSignature
    {
        return SalespersonSignature::create($data);
    }

    public function update(array $data, string $uuid): SalespersonSignature
    {
        $signature = $this->getByUuid($uuid);
        $signature->update($data);
        return $signature;
    }

    public function delete(string $uuid): bool
    {
        $signature = $this->getByUuid($uuid);
        return $signature->delete();
    }

    public function isSuperAdmin(int $userId): bool
    {
        return User::findOrFail($userId)->hasRole('Super Admin','api');
    }

    public function signatureExistsForOtherSalesperson(string $salesPersonId, ?string $excludeUuid = null): bool
    {
        $query = SalespersonSignature::where('salesperson_id', $salesPersonId);
        
        if ($excludeUuid) {
            $query->where('uuid', '!=', $excludeUuid);
        }
        
        return $query->exists();
    }
}