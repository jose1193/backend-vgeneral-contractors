<?php

namespace App\Repositories;
use App\Models\ScopeSheetPresentation;


use App\Interfaces\ScopeSheetPresentationRepositoryInterface;

class ScopeSheetPresentationRepository implements ScopeSheetPresentationRepositoryInterface
{
   /**
     * Get all scope sheets.
     */
    public function index()
    {
    return ScopeSheetPresentation::orderBy('photo_order', 'DESC')->get();
    }

    /**
     * Get a scope sheet by UUID.
     */

    /**
     * Get a scope sheet by UUID.
     */
    public function getByUuid(string $uuid)
    {
        return ScopeSheetPresentation::where('uuid', $uuid)->firstOrFail();
    }

    /**
     * Store a new scope sheet.
     */
    public function store(array $data)
    {
        return ScopeSheetPresentation::create($data);
    }

    /**
     * Update an existing scope sheet.
     */
    public function update(array $data, string $uuid)
    {
        $scopeSheet = ScopeSheetPresentation::where('uuid', $uuid)->firstOrFail();
        $scopeSheet->update($data);
        return $scopeSheet;
    }

    /**
     * Delete a scope sheet by UUID.
     */
    public function delete(string $uuid)
    {
        $scopeSheet = ScopeSheetPresentation::where('uuid', $uuid)->firstOrFail();
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
            return ScopeSheetPresentation::orderBy('id', 'DESC')->get();
        } else {
            // Otherwise, get only scope sheets associated with the user's claims
            return ScopeSheetPresentation::where('generated_by', $user->id)
                ->orderBy('id', 'DESC')
                ->get();
        }
    }

    public function getMaxPhotoOrder(int $scopeSheetId): ?int
    {
        return ScopeSheetPresentation::where('scope_sheet_id', $scopeSheetId)
            ->max('photo_order');
    }
    
    // Método para obtener los registros a actualizar
    public function getPhotoForReordering(int $scopeSheetId, int $deletedPhotoOrder)
    {
    return ScopeSheetPresentation::where('scope_sheet_id', $scopeSheetId)
        ->where('photo_order', '>', $deletedPhotoOrder)
        ->orderBy('photo_order')
        ->get();
    }


    public function countPhotosByType(int $scopeSheetId, string $photoType): int
    {
        return ScopeSheetPresentation::where('scope_sheet_id', $scopeSheetId)
            ->where('photo_type', $photoType)
            ->count();
    }


    public function updatePhotoOrder(int $scopeSheetId, array $orderedPhotoIds)
    {
    foreach ($orderedPhotoIds as $index => $photoId) {
        ScopeSheetPresentation::where('id', $photoId)
            ->where('scope_sheet_id', $scopeSheetId)
            ->update(['photo_order' => $index]); 
    }
    
    // Retornar las fotos actualizadas
    return ScopeSheetPresentation::where('scope_sheet_id', $scopeSheetId)
        ->orderBy('photo_order')
        ->get();
    }
}
