<?php

namespace App\Interfaces;

interface ScopeSheetExportRepositoryInterface
{
    public function index();
    public function getByUuid(string $uuid);
    public function store(array $data);
    public function update(array $data, string $uuid);
    public function delete(string $uuid);
    public function getByScopeSheetId(int $scopeSheetId);
    public function getByTemplateType(string $data);
}
