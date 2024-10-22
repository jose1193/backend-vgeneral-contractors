<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface PublicCompanyRepositoryInterface
{
    /**
     * Get all public companies
     *
     * @return Collection
     */
    public function index(): Collection;

    /**
     * Get a public company by UUID
     *
     * @param string $uuid
     * @return object
     */
    public function getByUuid(string $uuid): object;

    /**
     * Store a new public company
     *
     * @param array $data
     * @return object
     */
    public function store(array $data): object;

    /**
     * Update a public company
     *
     * @param array $data
     * @param string $uuid
     * @return object
     */
    public function update(array $data, string $uuid): object;

    /**
     * Delete a public company
     *
     * @param string $uuid
     * @return bool
     */
    public function delete(string $uuid): bool;

    public function findByName(string $name): ?object;
}