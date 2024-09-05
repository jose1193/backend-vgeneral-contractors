<?php

namespace App\Interfaces;

interface CompanySignatureRepositoryInterface
{
    public function index();
    public function getByUuid(string $uuid);
    public function store(array $data);
    public function update(array $data, $uuid);
    public function delete(string $uuid);
    
    public function findByUserId(int $userId);
    public function findFirst();
}
