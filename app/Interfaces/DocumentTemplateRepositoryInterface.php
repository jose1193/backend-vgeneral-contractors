<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface DocumentTemplateRepositoryInterface
{
    public function index(): Collection;

    public function getByUuid(string $uuid): ?object;

    public function getTemplatesByUser(object $user): Collection;

    public function store(array $data): object;

    public function update(array $data, string $uuid): object;

    public function delete(string $uuid): bool;

    public function getByUploadedBy(int $uploadedBy);

    public function isSuperAdmin(int $userId): bool;

    public function templateTypeExistsForOtherTemplate(string $templateType, ?string $excludeUuid = null): bool;

    public function getAllSuperAdmins(): Collection;

    /**
     * Check if a template type already exists
     */
    public function existsByType(string $type): bool;

    /**
     * Check if a template name already exists
     */
    public function existsByName(string $name): bool;

    /**
     * Check if a template name exists excluding a specific template UUID
     */
    public function existsByNameExcludingUuid(string $name, string $uuid): bool;
}