<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface AllianceCompanyRepositoryInterface 
{
    /**
     * Get all alliance companies
     *
     * @return Collection
     */
    public function index(): Collection;

    /**
     * Get an alliance company by UUID
     *
     * @param string $uuid
     * @return object
     */
    public function getByUuid(string $uuid): object;

    /**
     * Store a new alliance company
     *
     * @param array $data
     * @return object
     */
    public function store(array $data): object;

    /**
     * Update an alliance company
     *
     * @param array $data
     * @param string $uuid
     * @return object
     */
    public function update(array $data, string $uuid): object;

    /**
     * Delete an alliance company
     *
     * @param string $uuid
     * @return bool
     */
    public function delete(string $uuid): bool;

    /**
     * Find an alliance company by name
     *
     * @param string $name
     * @return object|null
     */
    public function findByName(string $name): ?object;
}