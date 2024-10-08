<?php

namespace App\Interfaces;

interface TypeDamageRepositoryInterface
{
    public function index();
    public function getByUuid(string $uuid);
    public function findByName(string $findName);
    public function store(array $data);
    public function isSuperAdmin(int $userId): bool;
    public function update(array $data, string $uuid);
    public function delete(string $uuid);
    
}
