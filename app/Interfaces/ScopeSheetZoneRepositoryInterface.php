<?php

namespace App\Interfaces;

interface ScopeSheetZoneRepositoryInterface
{
    public function index();
    public function getByUuid(string $uuid);
    public function store(array $data);
    public function update(array $data, string $uuid);
    public function delete(string $uuid);
    // Add this method to the interface
    public function getMaxZoneOrder(int $scopeSheetId): ?int;
     // Add this method to the interface
    public function getZonesForReordering(int $scopeSheetId, int $deletedZoneOrder);
}
