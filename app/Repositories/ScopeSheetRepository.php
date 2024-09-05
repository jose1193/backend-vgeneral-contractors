<?php

namespace App\Repositories;
use App\Models\ScopeSheet;


use App\Interfaces\ScopeSheetRepositoryInterface;



class ScopeSheetRepository implements ScopeSheetRepositoryInterface
{
    /**
     * Get all scope sheets.
     */
    public function index()
    {
        return ScopeSheet::orderBy('id', 'DESC')->get();
    }

    /**
     * Get a scope sheet by UUID.
     */

    /**
     * Get a scope sheet by UUID.
     */
    public function getByUuid(string $uuid)
    {
        return ScopeSheet::where('uuid', $uuid)->firstOrFail();
    }

    /**
     * Store a new scope sheet.
     */
    public function store(array $data)
    {
        return ScopeSheet::create($data);
    }

    /**
     * Update an existing scope sheet.
     */
    public function update(array $data, string $uuid)
    {
        $scopeSheet = ScopeSheet::where('uuid', $uuid)->firstOrFail();
        $scopeSheet->update($data);
        return $scopeSheet;
    }

    /**
     * Delete a scope sheet by UUID.
     */
    public function delete(string $uuid)
    {
        $scopeSheet = ScopeSheet::where('uuid', $uuid)->firstOrFail();
        $scopeSheet->delete();
        return $scopeSheet;
    }

    /**
     * Get scope sheets based on user permissions.
     */
    public function getByUser($user)
    {
        if ($user->hasPermissionTo('Director Assistant', 'api')) {
            // If the user has 'Director Assistant' permission, get all scope sheets
            return ScopeSheet::orderBy('id', 'DESC')->get();
        } else {
            // Otherwise, get only scope sheets associated with the user's claims
            return ScopeSheet::where('generated_by', $user->id)
                ->orderBy('id', 'DESC')
                ->get();
        }
    }

    /**
     * Find an existing scope sheet by claim ID and customer ID.
     */
    public function findExistingScope(int $claimId)
    {
        return ScopeSheet::where('claim_id', $claimId)->first();
    }
}