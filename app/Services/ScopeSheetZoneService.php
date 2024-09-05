<?php 

namespace App\Services;

use App\Http\Controllers\BaseController;
use App\Interfaces\ScopeSheetZoneRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Exception;

class ScopeSheetZoneService
{
    protected $serviceData;
    protected $baseController;
    protected $cacheTime = 720; // Cache duration in minutes

    public function __construct(
        ScopeSheetZoneRepositoryInterface $serviceData,
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
        $cacheKey = 'scope_sheet_zones_total_list_' . $userId;

        return $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($userId) {
            return $this->serviceData->index();
        });
    }

    public function storeData(array $details)
    {
        return $this->handleTransaction(function () use ($details) {
            try {
                // Generar un UUID para el nuevo registro
                $details['uuid'] = Uuid::uuid4()->toString();

                // Get the next available zone_order for the given scope_sheet_id
                $currentMaxZoneOrder = $this->serviceData->getMaxZoneOrder($details['scope_sheet_id']);
                $details['zone_order'] = $currentMaxZoneOrder ? $currentMaxZoneOrder + 1 : 1;

                // Almacenar la información del ScopeSheetZone en la base de datos
                $scopeSheetZone = $this->serviceData->store($details);

                $this->updateDataCache();
                return $scopeSheetZone;

            } catch (Exception $e) {
                // Manejar la excepción utilizando el método handleException
                $this->handleException($e, 'storing scope sheet zone');
            }
        });
    }

    public function updateData(array $updateDetails, string $uuid)
    {
        return $this->handleTransaction(function () use ($updateDetails, $uuid) {
            try {
                $existingScopeSheetZone = $this->serviceData->getByUuid($uuid);

                // Update the scope sheet zone information in the database
                $updatedScopeSheetZone = $this->serviceData->update($updateDetails, $uuid);

                $this->updateDataCache();
                return $updatedScopeSheetZone;

            } catch (Exception $e) {
                $this->handleException($e, 'updating scope sheet zone');
            }
        });
    }

    public function showData(string $uuid)
    {
        $cacheKey = 'scope_sheet_zone_' . $uuid;

        return $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($uuid) {
            try {
                return $this->serviceData->getByUuid($uuid);
            } catch (Exception $e) {
                $this->handleException($e, 'fetching scope sheet zone');
                return null;
            }
        });
    }

  
    public function deleteData(string $uuid)
    {
    return $this->handleTransaction(function () use ($uuid) {
        try {
            $cacheKey = 'scope_sheet_zone_' . $uuid;
            $existingScopeSheetZone = $this->serviceData->getByUuid($uuid);

            if (!$existingScopeSheetZone) {
                throw new Exception("Scope sheet zone not found");
            }

            // Almacenar scope_sheet_id y zone_order antes de eliminar
            $scopeSheetId = $existingScopeSheetZone->scope_sheet_id;
            $deletedZoneOrder = $existingScopeSheetZone->zone_order;

            // Eliminar el registro de la base de datos
            $this->serviceData->delete($uuid);

            // Actualizar zone_order para los elementos restantes
            $this->updateRemainingZoneOrders($scopeSheetId, $deletedZoneOrder);

            // Invalidar la caché
            $this->baseController->invalidateCache($cacheKey);
            $this->updateDataCache();

        } catch (Exception $e) {
            $this->handleException($e, 'deleting scope sheet zone');
            throw $e;
        }
    });
    }

    private function updateRemainingZoneOrders(int $scopeSheetId, int $deletedZoneOrder)
    {
    // Obtener todos los registros restantes con zone_order mayor al del registro eliminado
    $remainingZones = $this->serviceData->getZonesForReordering($scopeSheetId, $deletedZoneOrder);

    // Disminuir el zone_order de cada registro restante en 1
    foreach ($remainingZones as $zone) {
        $newZoneOrder = $zone->zone_order - 1;
        $this->serviceData->update(['zone_order' => $newZoneOrder], $zone->uuid);
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
        $cacheKey = 'scope_sheet_zones_total_list_' . $userId;

        if (!empty($cacheKey)) {
            $this->baseController->refreshCache($cacheKey, $this->cacheTime, function () {
                return $this->serviceData->getByUser(Auth::user());
            });
        } else {
            throw new \Exception('Invalid cacheKey provided');
        }
    }
}
