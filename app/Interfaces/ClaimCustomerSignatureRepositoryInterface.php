<?php

namespace App\Interfaces;

interface ClaimCustomerSignatureRepositoryInterface
{
    public function index();
    public function getByUuid(string $uuid);
    public function getClaimByUuid(string $uuid);
    public function store(array $data);
    public function update(array $data, string $uuid);
    public function delete(string $uuid);
    public function getSignaturesByUser($userId);
    public function findExistingSignature(int $claimId, int $customerId);
}
