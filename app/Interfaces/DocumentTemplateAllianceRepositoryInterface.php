<?php

namespace App\Interfaces;

interface DocumentTemplateAllianceRepositoryInterface
{
    public function index();
    public function getByUuid(string $uuid);
    public function store(array $data);
    public function update(array $data, string $uuid);
    public function delete(string $uuid);
    public function getDocumentTemplateAlliancesByUser(string $uuid);
    public function getCompanySignature();
}
