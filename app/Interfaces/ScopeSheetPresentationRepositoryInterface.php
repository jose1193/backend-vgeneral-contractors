<?php

namespace App\Interfaces;

interface ScopeSheetPresentationRepositoryInterface
{
    public function index();
    public function getByUuid(string $uuid);
    public function store(array $data);
    public function update(array $data, string $uuid);
    public function delete(string $uuid);
    public function countPhotosByType(int $scopeSheetId, string $photoType);
    public function updatePhotoOrder(int $scopeSheetZoneId, array $orderedPhotoIds);
    
}
