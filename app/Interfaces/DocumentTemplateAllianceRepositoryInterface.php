<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface DocumentTemplateAllianceRepositoryInterface
{
    /**
     * Get all document template alliances
     */
    public function index(): Collection;

    /**
     * Get a document template alliance by UUID
     */
    public function getByUuid(string $uuid): ?object;

    /**
     * Get document template alliances by user
     */
    public function getDocumentTemplateAlliancesByUser(string $uuid): Collection;

    /**
     * Store a new document template alliance
     */
    public function store(array $data): object;

    /**
     * Update an existing document template alliance
     */
    public function update(array $data, string $uuid): object;

    /**
     * Delete a document template alliance
     */
    public function delete(string $uuid): bool;

    /**
     * Get document template alliances by uploaded by
     */
    public function getByUploadedBy(int $uploadedBy): Collection;

    /**
     * Check if user is Super Admin
     */
    public function isSuperAdmin(int $userId): bool;

    /**
     * Check if template type exists for other templates
     */
    public function templateTypeExistsForOtherTemplate(string $templateType, ?string $excludeUuid = null): bool;

    /**
     * Get all Super Admins
     */
    public function getAllSuperAdmins(): Collection;

    /**
     * Get company signature
     */
    public function getCompanySignature(): ?object;

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