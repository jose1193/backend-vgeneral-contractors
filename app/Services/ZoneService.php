<?php // app/Services/Zone.php
namespace App\Services;

use App\Interfaces\CustomerRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Auth;
use App\Models\Zone;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;


class ZoneService
{
    protected $baseController;
    protected $zoneRepositoryInterface;
    protected $cacheKey;
    protected $cacheTime = 720;
    protected $userId;
    
    public function __construct(ZoneRepositoryInterface $zoneRepositoryInterface, BaseController $baseController)
    {
        $this->zoneRepositoryInterface = $zoneRepositoryInterface;
        $this->baseController = $baseController;
    }

    public function all()
    {
        try {
            $this->userId = Auth::id();
            $this->cacheKey = 'zones_' . $this->userId . '_total_list';

            $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () {
                return $this->zoneRepositoryInterface->index();
            });

            $data = Cache::get($this->cacheKey);

            if ($data === null || !is_iterable($data)) {
                Log::warning('Data fetched from cache is null or not iterable');
                return null;
            }

            return $data;

        } catch (QueryException $e) {
            Log::error('Database error occurred while fetching zones: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e;

        } catch (Exception $e) {
            Log::error('Error occurred while fetching zones: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e;
        }
    }

    public function updateZone(array $updateDetails, string $uuid)
    {
        DB::beginTransaction();

        try {
            $zone = $this->zoneRepositoryInterface->getByUuid($uuid);
            $zone = $this->zoneRepositoryInterface->update($updateDetails, $uuid);

            $this->updateZonesCache();

            DB::commit();

            return $zone;
        } catch (Exception $ex) {
            DB::rollBack();
            throw new Exception('Error occurred while updating zone: ' . $ex->getMessage());
        }
    }

    private function updateZonesCache()
    {
        $this->userId = Auth::id();
        $this->cacheKey = 'zones_' . $this->userId . '_total_list';

        if (!empty($this->cacheKey)) {
            $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () {
                return Zone::withTrashed()->orderBy('id', 'DESC')->get();
            });
        } else {
            throw new Exception('Invalid cacheKey provided');
        }
    }

    public function storeZone(array $details)
    {
        DB::beginTransaction();

        try {
            $details['uuid'] = Uuid::uuid4()->toString();
            $details['user_id'] = Auth::id();

            $zone = $this->zoneRepositoryInterface->store($details);

            $this->updateZonesCache();

            DB::commit();

            return $zone;
        } catch (Exception $ex) {
            DB::rollBack();
            throw new Exception('Error occurred while creating zone: ' . $ex->getMessage());
        }
    }

    public function showZone(string $uuid)
    {
        try {
            $cacheKey = 'zone_' . $uuid;

            $zone = $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($uuid) {
                return $this->zoneRepositoryInterface->getByUuid($uuid);
            });

            return $zone;

        } catch (Exception $ex) {
            throw new Exception('Error occurred while retrieving zone: ' . $ex->getMessage());
        }
    }

    public function deleteZone(string $uuid)
    {
        try {
            $zone = $this->zoneRepositoryInterface->delete($uuid);

            $this->baseController->invalidateCache('zone_' . $uuid);

            $this->updateZonesCache();

            return $zone;
        } catch (Exception $ex) {
            throw new Exception('Error occurred while deleting zone: ' . $ex->getMessage());
        }
    }

    public function restoreZone(string $uuid)
    {
        try {
            $zone = $this->zoneRepositoryInterface->restore($uuid);

            $this->baseController->invalidateCache('zone_' . $uuid);

            $this->updateZonesCache();

            return $zone;
        } catch (Exception $ex) {
            throw new Exception('Error occurred while restoring zone: ' . $ex->getMessage());
        }
    }
}