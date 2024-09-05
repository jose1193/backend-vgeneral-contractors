<?php

namespace App\Interfaces;

interface ClaimAgreementFullRepositoryInterface
{
    public function index();
    public function getByUuid(string $uuid);
    public function getClaimByUuid(string $uuid);
    public function store(array $data);
    public function update(array $data, string $uuid);
    public function delete(string $uuid);
    public function getClaimAgreementByUser($userId);
    public function getByTemplateType(string $data);
}
