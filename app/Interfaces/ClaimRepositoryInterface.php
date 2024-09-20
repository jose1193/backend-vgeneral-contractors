<?php

namespace App\Interfaces;

interface ClaimRepositoryInterface
{
    public function index();
    public function getByUuid(string $uuid);
    public function store(array $data);
    public function update(array $data, string $uuid);
    public function delete(string $uuid);
    public function getClaimsByUser($userId);
    public function storeAffidavitForm(array $data, int $claimId);
    public function updateAffidavitForm(array $data, int $claimId);
    public function restore(string $uuid);
    public function getSignaturePathId();
   
}
