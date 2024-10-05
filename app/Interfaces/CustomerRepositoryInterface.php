<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface CustomerRepositoryInterface
{
    public function index(): Collection;
    public function getByUuid(string $uuid): object;
    public function getByUser(object $userId);
    public function store(array $data): object;
    public function update(array $data, string $uuid): object;
    public function delete(string $uuid): bool;
    public function restore(string $uuid): object;
    public function isSuperAdmin(int $userId): bool;
    public function emailExistsForOtherCustomer(string $email, ?string $excludeUuid = null): bool;
    
    public function emailExists(string $email): bool;
    
    public function getAllSuperAdmins(): Collection;
}