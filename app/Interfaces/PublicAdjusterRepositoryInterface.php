<?php

namespace App\Interfaces;

interface PublicAdjusterRepositoryInterface
{
    public function index();
    public function getByUuid(string $uuid);
    public function store(array $data);
    public function update(array $data, $uuid);
    public function delete(string $uuid);
    public function getByUserIdAndCompanyIdExceptCurrent(int $userId, int $companyId, string $excludeUuid);
    public function getByUserIdAndCompanyId(int $userId, int $companyId);
}
