<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface SalespersonSignatureRepositoryInterface 
{
    public function index(): Collection;
    
    public function getByUuid(string $uuid): ?object;
    
    public function getSignaturesByUser(object $user): Collection;
    
    public function store(array $data): object;
    
    public function update(array $data, string $uuid): object;
    
    public function delete(string $uuid): bool;
    
    public function getBySalespersonId(int $salespersonId);

    public function isSuperAdmin(int $userId): bool;
    
    public function signatureExistsForOtherSalesperson(string $sellerId, ?string $excludeUuid = null): bool;

    public function getAllSuperAdmins(): Collection;
}