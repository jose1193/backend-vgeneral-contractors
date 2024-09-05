<?php

namespace App\Repositories;
use App\Models\ScopeSheetZonePhoto;

use App\Interfaces\ScopeSheetZonePhotoRepositoryInterface;

class ScopeSheetZonePhotoRepository implements ScopeSheetZonePhotoRepositoryInterface
{
    /**
     * Get all scope sheets.
     */
    public function index()
    {
    return ScopeSheetZonePhoto::orderBy('photo_order', 'DESC')->get();
    }

    /**
     * Get a scope sheet by UUID.
     */

    /**
     * Get a scope sheet by UUID.
     */
    public function getByUuid(string $uuid)
    {
        return ScopeSheetZonePhoto::where('uuid', $uuid)->firstOrFail();
    }

    /**
     * Store a new scope sheet.
     */
    public function store(array $data)
    {
        return ScopeSheetZonePhoto::create($data);
    }

    /**
     * Update an existing scope sheet.
     */
    public function update(array $data, string $uuid)
    {
        $scopeSheet = ScopeSheetZonePhoto::where('uuid', $uuid)->firstOrFail();
        $scopeSheet->update($data);
        return $scopeSheet;
    }

    /**
     * Delete a scope sheet by UUID.
     */
    public function delete(string $uuid)
    {
        $scopeSheet = ScopeSheetZonePhoto::where('uuid', $uuid)->firstOrFail();
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
            return ScopeSheetZonePhoto::orderBy('id', 'DESC')->get();
        } else {
            // Otherwise, get only scope sheets associated with the user's claims
            return ScopeSheetZonePhoto::where('generated_by', $user->id)
                ->orderBy('id', 'DESC')
                ->get();
        }
    }

    public function getMaxPhotoOrder(int $scopeSheeZonetId): ?int
    {
        return ScopeSheetZonePhoto::where('scope_sheet_zone_id', $scopeSheeZonetId)
            ->max('photo_order');
    }
    
    // Método para obtener los registros a actualizar
    public function getPhotoForReordering(int $scopeSheeZonetId, int $deletedPhotoOrder)
    {
    return ScopeSheetZonePhoto::where('scope_sheet_zone_id', $scopeSheeZonetId)
        ->where('photo_order', '>', $deletedPhotoOrder)
        ->orderBy('photo_order')
        ->get();
    }


    


  public function updatePhotoOrder(int $scopeSheetZoneId, array $orderedPhotoIds)
    {
    foreach ($orderedPhotoIds as $index => $photoId) {
        ScopeSheetZonePhoto::where('id', $photoId)
            ->where('scope_sheet_zone_id', $scopeSheetZoneId)
            ->update(['photo_order' => $index]); 
    }
    
    // Retornar las fotos actualizadas
    return ScopeSheetZonePhoto::where('scope_sheet_zone_id', $scopeSheetZoneId)
        ->orderBy('photo_order')
        ->get();
    }



}
