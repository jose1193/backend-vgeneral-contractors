<?php

namespace App\Repositories;
use App\Models\ScopeSheetZone;


use App\Interfaces\ScopeSheetZoneRepositoryInterface;



class ScopeSheetZoneRepository implements ScopeSheetZoneRepositoryInterface
{
    /**
     * Get all scope sheets.
     */
    public function index()
    {
        return ScopeSheetZone::orderBy('id', 'DESC')->get();
    }

    /**
     * Get a scope sheet by UUID.
     */

    /**
     * Get a scope sheet by UUID.
     */
    public function getByUuid(string $uuid)
    {
        return ScopeSheetZone::where('uuid', $uuid)->firstOrFail();
    }

    /**
     * Store a new scope sheet.
     */
    public function store(array $data)
    {
        return ScopeSheetZone::create($data);
    }

    /**
     * Update an existing scope sheet.
     */
    public function update(array $data, string $uuid)
    {
        $scopeSheet = ScopeSheetZone::where('uuid', $uuid)->firstOrFail();
        $scopeSheet->update($data);
        return $scopeSheet;
    }

    /**
     * Delete a scope sheet by UUID.
     */
    public function delete(string $uuid)
    {
        $scopeSheet = ScopeSheetZone::where('uuid', $uuid)->firstOrFail();
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
            return ScopeSheetZone::orderBy('id', 'DESC')->get();
        } else {
            // Otherwise, get only scope sheets associated with the user's claims
            return ScopeSheetZone::where('generated_by', $user->id)
                ->orderBy('id', 'DESC')
                ->get();
        }
    }

    public function getMaxZoneOrder(int $scopeSheetId): ?int
    {
        return ScopeSheetZone::where('scope_sheet_id', $scopeSheetId)
            ->max('zone_order');
    }
    
    // Método para obtener los registros a actualizar
    public function getZonesForReordering(int $scopeSheetId, int $deletedZoneOrder)
    {
    return ScopeSheetZone::where('scope_sheet_id', $scopeSheetId)
        ->where('zone_order', '>', $deletedZoneOrder)
        ->orderBy('zone_order')
        ->get();
    }

}