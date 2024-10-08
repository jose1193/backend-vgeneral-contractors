<?php

namespace App\Repositories;

use App\Models\ClaimStatu;
use App\Models\User;
use App\Interfaces\ClaimStatusRepositoryInterface;

class ClaimStatusRepository implements ClaimStatusRepositoryInterface
{
    /**
     * Get all claim statuses ordered by the most recent.
     */
    public function index()
    {
        return ClaimStatu::orderBy('id', 'DESC')->get();
    }

    /**
     * Get a specific claim status by UUID.
     */
    public function getByUuid(string $uuid)
    {
        return ClaimStatu::where('uuid', $uuid)->firstOrFail();
    }

    /**
     * Get a claim status by its name.
     */
    public function findByName(string $claimStatusName)
    {
        return ClaimStatu::where('claim_status_name', $claimStatusName)->first();
    }

    /**
     * Check if the user is a Super Admin.
     */
    public function isSuperAdmin(int $userId): bool
    {
        return User::findOrFail($userId)->hasRole('Super Admin');
    }

    /**
     * Store a new claim status.
     */
    public function store(array $data)
    {
        return ClaimStatu::create($data);
    }

    /**
     * Update an existing claim status by UUID.
     */
    public function update(array $data, string $uuid)
    {
        $claimStatus = $this->getByUuid($uuid);
        $claimStatus->update($data);
        return $claimStatus;
    }

    /**
     * Delete a claim status by UUID.
     */
    public function delete(string $uuid)
    {
        $claimStatus = $this->getByUuid($uuid);
        $claimStatus->delete();
        return $claimStatus;
    }
}
