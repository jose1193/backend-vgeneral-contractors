<?php

namespace App\Repositories;

use App\Models\PublicCompany;
use App\Models\User;
use App\Interfaces\PublicCompanyRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PublicCompanyRepository implements PublicCompanyRepositoryInterface
{
    /**
     * Get all public companies.
     *
     * @return Collection
     */
    public function index(): Collection
    {
        return PublicCompany::orderBy('id', 'DESC')->get();
    }

    /**
     * Get a public company by UUID.
     *
     * @param string $uuid
     * @return PublicCompany
     * @throws ModelNotFoundException
     */
    public function getByUuid(string $uuid): PublicCompany
    {
        return PublicCompany::where('uuid', $uuid)->firstOrFail();
    }

    /**
     * Store a new public company.
     *
     * @param array $data
     * @return PublicCompany
     */
    public function store(array $data): PublicCompany
    {
        return PublicCompany::create($data);
    }

    /**
     * Update a public company.
     *
     * @param array $data
     * @param string $uuid
     * @return PublicCompany
     * @throws ModelNotFoundException
     */
    public function update(array $data, string $uuid): PublicCompany
    {
        $publicCompany = $this->getByUuid($uuid);
        $publicCompany->update($data);
        return $publicCompany;
    }

    /**
     * Delete a public company.
     *
     * @param string $uuid
     * @return bool
     * @throws ModelNotFoundException
     */
    public function delete(string $uuid): bool
    {
        $publicCompany = $this->getByUuid($uuid);
        return $publicCompany->delete();
    }

    public function findByName(string $name): ?object
    {
        return PublicCompany::where('public_company_name', $name)->first();
    }

     public function isSuperAdmin(int $userId): bool
    {
        return User::findOrFail($userId)->hasRole('Super Admin');
    }
}