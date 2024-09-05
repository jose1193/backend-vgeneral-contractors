<?php

namespace App\Interfaces;

interface SalespersonSignatureRepositoryInterface 
{
    public function index();
    public function getByUuid(string $uuid);
    public function store(array $data);
    public function update(array $data, string $uuid);
    public function delete(string $uuid);
    public function getSignaturesByUser($user);
}
