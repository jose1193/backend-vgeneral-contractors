<?php

namespace App\Interfaces;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UsersRepositoryInterface
{
    public function index();
    public function getByUuid(string $uuid): ?object;
    public function store(array $data): object;
    public function update(array $data, string $uuid): object;
    public function delete(string $uuid): bool;
    public function restore(string $uuid): object;
    public function getByRole(string $role);
    public function findByEmail(string $email): ?User;
    public function isSuperAdmin(int $userId): bool;

    public function getAllSuperAdmins(): Collection;
    public function getAllRoles(): Collection;
}
