<?php

namespace App\Repositories;

use App\Models\AllianceCompany;
use App\Interfaces\AllianceCompanyRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Models\User;


class AllianceCompanyRepository implements AllianceCompanyRepositoryInterface
{
    /**
     * Get all alliance companies.
     *
     * @return Collection
     */
    public function index(): Collection
    {
        return AllianceCompany::orderBy('id', 'DESC')->get();
    }

    /**
     * Get an alliance company by UUID.
     *
     * @param string $uuid
     * @return AllianceCompany
     * @throws ModelNotFoundException
     */
    public function getByUuid(string $uuid): AllianceCompany
    {
        return AllianceCompany::where('uuid', $uuid)->firstOrFail();
    }

    /**
     * Store a new alliance company.
     *
     * @param array $data
     * @return AllianceCompany
     */
    public function store(array $data): AllianceCompany
    {
        return AllianceCompany::create($data);
    }

    /**
     * Update an alliance company.
     *
     * @param array $data
     * @param string $uuid
     * @return AllianceCompany
     * @throws ModelNotFoundException
     */
    public function update(array $data, string $uuid): AllianceCompany
    {
        $allianceCompany = $this->getByUuid($uuid);
        $allianceCompany->update($data);
        return $allianceCompany;
    }

    /**
     * Delete an alliance company.
     *
     * @param string $uuid
     * @return bool
     * @throws ModelNotFoundException
     */
    public function delete(string $uuid): bool
    {
        $allianceCompany = $this->getByUuid($uuid);
        return $allianceCompany->delete();
    }

    /**
     * Find an alliance company by name.
     *
     * @param string $name
     * @return AllianceCompany|null
     */
    public function findByName(string $name): ?AllianceCompany
    {
        return AllianceCompany::where('alliance_company_name', $name)->first();
    }

    public function isSuperAdmin(int $userId): bool
    {
        return User::findOrFail($userId)->hasRole('Super Admin');
    }
}