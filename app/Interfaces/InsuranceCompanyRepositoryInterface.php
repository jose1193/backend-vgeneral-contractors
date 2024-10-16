<?php

namespace App\Interfaces;

interface InsuranceCompanyRepositoryInterface
{
    public function index();
    public function getByUuid(string $uuid);
    public function findByName(string $name): ?object;
    public function isSuperAdmin(int $userId): bool;
    public function store(array $data);
    public function update(array $data, string $uuid);
    public function delete(string $uuid);
}