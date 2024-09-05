<?php

namespace App\Services;

use App\Http\Controllers\BaseController;
use App\Interfaces\ScopeSheetPresentationRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Classes\ApiResponseClass;
use Ramsey\Uuid\Uuid;
use Exception;
use App\Helpers\ImageHelper;

class ScopeSheetPresentationService
{
    protected $serviceData;
    protected $baseController;
    protected $cacheTime = 720; // Cache duration in minutes

    public function __construct(
        ScopeSheetPresentationRepositoryInterface $serviceData,
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
        $cacheKey = 'scope_sheets_presentation_total_list_' . $userId;

        return $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($userId) {
            return $this->serviceData->index();
        });
    }

      public function storeData(array $details)
    {
        return $this->handleTransaction(function () use ($details) {
        try {
            $this->validatePhotoType($details['photo_type']);
            $this->checkPhotoLimit($details);

            $details['photo_order'] = $this->determineStartingPhotoOrder($details);

            $this->ensurePhotoPathIsArray($details['photo_path']);

            $storedPhotos = array_map(function ($photoPath) use (&$details) {
                $storedPhoto = $this->storeSinglePhoto($details, $photoPath);
                if ($details['photo_type'] !== 'house_number') {
                    $details['photo_order']++;
                }
                return $storedPhoto;
            }, $details['photo_path']);

            $this->updateDataCache();

            return $storedPhotos;
        } catch (Exception $e) {
            $this->handleException($e, 'storing scope sheet presentation');
            throw $e;
        }
        });
    }

    public function updateData(array $updateDetails, string $uuid)
    {
        return $this->handleTransaction(function () use ($updateDetails, $uuid) {
        try {
            $existingScopeSheet = $this->serviceData->getByUuid($uuid);

            if (!$existingScopeSheet) {
                Log::warning('ScopeSheetPresentation not found', ['uuid' => $uuid]);
                throw new Exception('ScopeSheetPresentation not found.');
            }

            if (isset($updateDetails['photo_path'])) {
                $this->validateSinglePhotoUpdate($updateDetails['photo_path']);
                $updateDetails['photo_path'] = $this->replaceImageInS3($existingScopeSheet, $updateDetails['photo_path'][0]);
            }

            $updatedScopeSheet = $this->serviceData->update($updateDetails, $uuid);

            Log::info('Update successful', ['updatedScopeSheet' => $updatedScopeSheet]);

            $this->updateDataCache();

            return $updatedScopeSheet;
        } catch (Exception $e) {
            $this->handleException($e, 'updating scope sheet presentation');
            throw $e;
        }
        });
    }

    private function validateSinglePhotoUpdate(array $photoPaths)
    {
    if (count($photoPaths) > 1) {
        throw new Exception('Only one photo can be updated at a time.');
    }
    }

    private function validatePhotoType(string $photoType)
    {
    $validPhotoTypes = ['front_house', 'house_number'];
    if (!in_array($photoType, $validPhotoTypes)) {
        throw new Exception('Invalid photo type.');
    }
    }

    private function checkPhotoLimit(array $details)
    {
    $photoCount = $this->serviceData->countPhotosByType($details['scope_sheet_id'], $details['photo_type']);
    $maxPhotos = ($details['photo_type'] === 'front_house') ? 3 : 1;

    if ($photoCount >= $maxPhotos) {
        throw new Exception("Cannot add more than {$maxPhotos} photos of type \"{$details['photo_type']}\".");
      }
    }

    private function determineStartingPhotoOrder(array $details): int
    {
        if ($details['photo_type'] === 'house_number') {
        return 1;
        }

        $currentMaxPhotoOrder = $this->serviceData->getMaxPhotoOrder($details['scope_sheet_id']);
        return $currentMaxPhotoOrder ? $currentMaxPhotoOrder + 1 : 1;
    }

    private function ensurePhotoPathIsArray($photoPath)
    {
        if (!is_array($photoPath)) {
        throw new Exception('Photo path should be an array.');
        }
    }

    private function storeSinglePhoto(array $details, string $photoPath)
    {
    $details['uuid'] = Uuid::uuid4()->toString();
    $details['photo_path'] = ImageHelper::storeAndResize($photoPath, 'public/scope_sheet_photos');

        return $this->serviceData->store($details);
    }

    private function replaceImageInS3($existingScopeSheet, $newPhotoPath)
    {
        if ($existingScopeSheet->photo_path && $existingScopeSheet->photo_path !== $newPhotoPath) {
        ImageHelper::deleteFileFromStorage($existingScopeSheet->photo_path);
        }

        return ImageHelper::storeAndResize($newPhotoPath, 'public/scope_sheet_photos');
    }



   public function reorderImages(int $scopeSheetId, array $orderedPhotoIds)
    {
    return $this->handleTransaction(function () use ($scopeSheetId, $orderedPhotoIds) {
        // Llama al método de servicio que actualiza el orden de las fotos
        return $this->serviceData->updatePhotoOrder($scopeSheetId, $orderedPhotoIds);
    });
    }




    public function showData(string $uuid)
    {
        $cacheKey = 'scope_sheet_presentation_' . $uuid;

        return $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($uuid) {
            try {
                return $this->serviceData->getByUuid($uuid);
            } catch (Exception $e) {
                $this->handleException($e, 'fetching scope sheet presentation');
                return null;
            }
        });
    }

        public function deleteData(string $uuid)
    {
    return $this->handleTransaction(function () use ($uuid) {
        try {
            $cacheKey = 'scope_sheet_presentation_' . $uuid;
            $existingScopeSheet = $this->serviceData->getByUuid($uuid);

            if (!$existingScopeSheet) {
                throw new Exception("ScopeSheetPresentation not found");
            }

            // Obtener el tipo de foto antes de eliminarla
            $photoType = $existingScopeSheet->photo_type;
            $scopeSheetId = $existingScopeSheet->scope_sheet_id;
            $deletedPhotoOrder = $existingScopeSheet->photo_order;

            // Eliminar la foto de S3
            ImageHelper::deleteFileFromStorage($existingScopeSheet->photo_path);

            // Eliminar el scope sheet presentation de la base de datos
            $this->serviceData->delete($uuid);

            // Si la foto es de tipo 'front_house', reordenar las restantes
            if ($photoType === 'front_house') {
                $this->updateRemainingPhotoOrders($scopeSheetId, $deletedPhotoOrder);
            }

            // Invalidar la caché
            $this->baseController->invalidateCache($cacheKey);
            $this->updateDataCache();

        } catch (Exception $e) {
            $this->handleException($e, 'deleting scope sheet presentation');
            throw $e;
        }
    });
    }

    private function updateRemainingPhotoOrders(int $scopeSheetId, int $deletedPhotoOrder)
    {
    // Obtener todos los registros restantes con photo_order mayor al del registro eliminado
    $remainingPhotos = $this->serviceData->getPhotosForReordering($scopeSheetId, $deletedPhotoOrder);

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
        $cacheKey = 'scope_sheets_total_list_' . $userId;

        if (!empty($cacheKey)) {
            $this->baseController->refreshCache($cacheKey, $this->cacheTime, function () {
                return $this->serviceData->getByUser(Auth::user());
            });
        } else {
            throw new Exception('Invalid cacheKey provided');
        }
    }
}
