<?php

namespace App\Services;

use App\Http\Controllers\BaseController;
use App\Interfaces\ScopeSheetZonePhotoRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Classes\ApiResponseClass;
use Ramsey\Uuid\Uuid;
use Exception;
use App\Helpers\ImageHelper;

class ScopeSheetZonePhotoService
{
    protected $serviceData;
    protected $baseController;
    protected $cacheTime = 720; // Cache duration in minutes

    public function __construct(
        ScopeSheetZonePhotoRepositoryInterface $serviceData,
        BaseController $baseController
    ) {
        $this->serviceData = $serviceData;
        $this->baseController = $baseController;
    }

    private function getUserId()
    {
        return Auth::id();
    }

    public function all()
    {
        $userId = $this->getUserId();
        $cacheKey = 'scope_sheets_zone_photos_total_list_' . $userId;

        return $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($userId) {
            return $this->serviceData->index();
        });
    }

    public function storeData(array $details)
    {
        return $this->handleTransaction(function () use ($details) {
        $this->validatePhotoPath($details['photo_path']);
        
        $nextPhotoOrder = $this->getNextPhotoOrder($details['scope_sheet_zone_id']);

        $storedPhotos = array_map(function ($photoPath) use ($details, &$nextPhotoOrder) {
            return $this->storePhoto($details, $photoPath, $nextPhotoOrder++);
        }, $details['photo_path']);

        $this->updateDataCache();
        return $storedPhotos;
        });
    }
     private function validatePhotoPath($photoPath)
    {
        if (!is_array($photoPath)) {
        throw new Exception('Photo path should be an array.');
        }
    }

    private function getNextPhotoOrder($scopeSheetZoneId): int
    {
    $currentMaxPhotoOrder = $this->serviceData->getMaxPhotoOrder($scopeSheetZoneId);
    return $currentMaxPhotoOrder ? $currentMaxPhotoOrder + 1 : 1;
    }

    private function storePhoto($details, $photoPath, int $photoOrder)
    {
        $uuid = Uuid::uuid4()->toString();
        $newPhotoUrl = ImageHelper::storeAndResize($photoPath, 'public/scope_sheet_zone_photos');

        $data = array_merge($details, [
        'photo_path' => $newPhotoUrl,
        'uuid' => $uuid,
        'photo_order' => $photoOrder,
        ]);

        return $this->serviceData->store($data);
    }



    public function updateData(array $updateDetails, string $uuid)
    {
        return $this->handleTransaction(function () use ($updateDetails, $uuid) {
            $existingScopeSheetZonePhoto = $this->getExistingPhoto($uuid);

            if (isset($updateDetails['photo_path'])) {
                $this->validateSinglePhotoUpdate($updateDetails['photo_path']);
                $updateDetails['photo_path'] = $this->updatePhoto($existingScopeSheetZonePhoto, $updateDetails['photo_path'][0]);
            }

            $updatedScopeSheetZonePhoto = $this->serviceData->update($updateDetails, $uuid);
            $this->updateDataCache();

            return $updatedScopeSheetZonePhoto;
        });
    }

    

    

    private function getExistingPhoto($uuid)
    {
        $existingScopeSheetZonePhoto = $this->serviceData->getByUuid($uuid);

        if (!$existingScopeSheetZonePhoto) {
            Log::warning('ScopeSheetZonePhoto not found', ['uuid' => $uuid]);
            throw new Exception('ScopeSheetZonePhoto not found.');
        }

        return $existingScopeSheetZonePhoto;
    }

    private function validateSinglePhotoUpdate($photoPath)
    {
        if (!is_array($photoPath) || count($photoPath) > 1) {
            throw new Exception('Only one photo can be updated at a time.');
        }
    }

    private function updatePhoto($existingScopeSheetZonePhoto, $newPhotoPath)
    {
        if ($existingScopeSheetZonePhoto->photo_path && $existingScopeSheetZonePhoto->photo_path !== $newPhotoPath) {
            ImageHelper::deleteFileFromStorage($existingScopeSheetZonePhoto->photo_path);
        }

        return ImageHelper::storeAndResize($newPhotoPath, 'public/scope_sheet_zone_photos');
    }

   



   public function reorderImages(int $scopeSheetZoneId, array $orderedPhotoIds)
{
    return $this->handleTransaction(function () use ($scopeSheetZoneId, $orderedPhotoIds) {
        return $this->serviceData->updatePhotoOrder($scopeSheetZoneId, $orderedPhotoIds);
    });
}



    public function showData(string $uuid)
    {
        $cacheKey = 'scope_sheet_zone_photo_' . $uuid;

        return $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($uuid) {
            try {
                return $this->serviceData->getByUuid($uuid);
            } catch (Exception $e) {
                $this->handleException($e, 'fetching scope sheet zone photo');
                return null;
            }
        });
    }

    public function deleteData(string $uuid)
    {
        return $this->handleTransaction(function () use ($uuid) {
            try {
                $cacheKey = 'scope_sheet_zone_photo_' . $uuid;
                $existingScopeSheetZonePhoto = $this->serviceData->getByUuid($uuid);

                if (!$existingScopeSheetZonePhoto) {
                    throw new Exception("ScopeSheetZonePhoto not found");
                }

                // Eliminar la foto de S3
                ImageHelper::deleteFileFromStorage($existingScopeSheetZonePhoto->photo_path);

                // Eliminar el scope sheet zone photo de la base de datos
                $this->serviceData->delete($uuid);

                // Reordenar las fotos restantes
                $this->updateRemainingPhotoOrders($existingScopeSheetZonePhoto->scope_sheet_zone_id, $existingScopeSheetZonePhoto->photo_order);

                // Invalidar la caché
                $this->baseController->invalidateCache($cacheKey);
                $this->updateDataCache();

            } catch (Exception $e) {
                $this->handleException($e, 'deleting scope sheet zone photo');
                throw $e;
            }
        });
    }

    private function updateRemainingPhotoOrders(int $scopeSheetZoneId, int $deletedPhotoOrder)
    {
        // Obtener todos los registros restantes con photo_order mayor al del registro eliminado
        $remainingPhotos = $this->serviceData->getPhotoForReordering($scopeSheetZoneId, $deletedPhotoOrder);

        // Disminuir el photo_order de cada registro restante en 1
        foreach ($remainingPhotos as $photo) {
            $newPhotoOrder = $photo->photo_order - 1;
            $this->serviceData->update(['photo_order' => $newPhotoOrder], $photo->uuid);
        }
    }  

    private function handleTransaction(callable $callback)
    {
        DB::beginTransaction();
        try {
            $result = $callback();
            DB::commit();
            return $result;
        } catch (Exception $ex) {
            DB::rollBack();
            $this->handleException($ex, 'transaction');
            throw $ex;
        }
    }

    private function handleException(Exception $e, string $context)
    {
        Log::error("Error occurred while {$context}: " . $e->getMessage(), [
            'exception' => $e,
            'stack_trace' => $e->getTraceAsString(),
            'user_id' => Auth::id(),
            'context' => $context
        ]);

        throw $e;
    }

    private function updateDataCache()
    {
        $userId = Auth::id();
        $cacheKey = 'scope_sheets_zone_photos_total_list_' . $userId;

        if (!empty($cacheKey)) {
            $this->baseController->refreshCache($cacheKey, $this->cacheTime, function () {
                return $this->serviceData->getByUser(Auth::user());
            });
        } else {
            throw new Exception('Invalid cacheKey provided');
        }
    }
}
