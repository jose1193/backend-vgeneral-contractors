<?php

namespace App\Interfaces;

interface UsersRepositoryInterface
{
    public function index();
    public function getByUuid(string $uuid);
    public function store(array $data);
    public function update(array $data, string $uuid): object;
    public function delete(string $uuid);
    public function restore(string $uuid);
    public function getByRole(string $role);
    public function isSuperAdmin(int $userId): bool;
}
