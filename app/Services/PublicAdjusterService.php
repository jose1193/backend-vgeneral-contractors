<?php // app/Services/PublicAdjusterService.php
namespace App\Services;

use App\Interfaces\PublicAdjusterRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;
use App\Models\PublicAdjuster;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\QueryException;


class PublicAdjusterService
{
    protected $baseController;
    protected $publicAdjusterRepositoryInterface;
    protected $cacheKey;
    protected $cacheTime = 720;
    protected $userId;

    public function __construct(PublicAdjusterRepositoryInterface $publicAdjusterRepositoryInterface, BaseController $baseController)
    {
        $this->publicAdjusterRepositoryInterface = $publicAdjusterRepositoryInterface;
        $this->baseController = $baseController;
    }

    public function all()
    {
        try {
            $this->userId = Auth::id();
            $this->cacheKey = 'public_adjusters_' . $this->userId . '_total_list';

            // Refrescar la caché si es necesario
            $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () {
                return $this->publicAdjusterRepositoryInterface->index();
            });

            // Obtener los datos actualizados desde la caché
            $data = Cache::get($this->cacheKey);

            // Verificar si la colección es válida
            if ($data === null || !is_iterable($data)) {
                Log::warning('Data fetched from cache is null or not iterable', [
                    'user_id' => $this->userId
                ]);
                return null;
            }

            return $data;

        } catch (QueryException $e) {
            Log::error('Database error occurred while fetching public adjusters: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => $this->userId
            ]);
            throw $e;

        } catch (\Exception $e) {
            Log::error('Error occurred while fetching public adjusters: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => $this->userId
            ]);
            throw $e;
        }
    }

    public function updatePublicAdjuster(array $updateDetails, string $uuid)
    {
        DB::beginTransaction();

        try {
            $adjuster = $this->publicAdjusterRepositoryInterface->getByUuid($uuid);

           

           $existingAdjuster = $this->publicAdjusterRepositoryInterface->getByUserIdAndCompanyIdExceptCurrent(
            $updateDetails['user_id'],
            $updateDetails['public_company_id'],
            $uuid
        );

        if ($existingAdjuster) {
            throw new \Exception('The user is already assigned to another company');
        }

            $adjuster = $this->publicAdjusterRepositoryInterface->update($updateDetails, $uuid);

            $this->updatePublicAdjustersCache();

            DB::commit();

            return $adjuster;
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception('Error occurred while updating public adjuster: ' . $ex->getMessage());
        }
    }

    private function updatePublicAdjustersCache()
    {
        $this->cacheKey = 'public_adjusters_total_list';

        if (!empty($this->cacheKey)) {
            $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () {
                return PublicAdjuster::orderBy('id', 'DESC')->get();
            });
        } else {
            throw new \Exception('Invalid cacheKey provided');
        }
    }

    public function storePublicAdjuster(array $details)
    {
        DB::beginTransaction();

        try {
            $details['uuid'] = Uuid::uuid4()->toString();
        
            // Verificar si ya existe una entrada exactamente igual
        $existingAdjuster = $this->publicAdjusterRepositoryInterface->getByUserIdAndCompanyId(
            $details['user_id'],
            $details['public_company_id']
        );

        if ($existingAdjuster) {
            throw new \Exception('This user is already assigned to this company');
        }

            $adjuster = $this->publicAdjusterRepositoryInterface->store($details);

            $this->updatePublicAdjustersCache();

            DB::commit();

            return $adjuster;
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception('Error occurred while creating public adjuster: ' . $ex->getMessage());
        }
    }

    public function showPublicAdjuster(string $uuid)
    {
        try {
            $cacheKey = 'public_adjuster_' . $uuid;

            $adjuster = $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($uuid) {
                return $this->publicAdjusterRepositoryInterface->getByUuid($uuid);
            });

            return $adjuster;

        } catch (\Exception $ex) {
            throw new \Exception('Error occurred while retrieving public adjuster: ' . $ex->getMessage());
        }
    }

    public function deletePublicAdjuster(string $uuid)
    {
        try {
            $adjuster = $this->publicAdjusterRepositoryInterface->delete($uuid);

            $this->baseController->invalidateCache('public_adjuster_' . $uuid);

            $this->updatePublicAdjustersCache();

            return $adjuster;
        } catch (\Exception $ex) {
            throw new \Exception('Error occurred while deleting public adjuster: ' . $ex->getMessage());
        }
    }
}