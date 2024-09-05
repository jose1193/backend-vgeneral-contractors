<?php // app/Services/InsuranceAdjusterService.php
namespace App\Services;

use App\Interfaces\InsuranceAdjusterRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\BaseController;
use App\Models\InsuranceAdjuster;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\QueryException;


class InsuranceAdjusterService
{
    protected $baseController;
    protected $insuranceAdjusterRepositoryInterface;
    protected $cacheKey;
    protected $cacheTime = 720;
    protected $userId;


    public function __construct(InsuranceAdjusterRepositoryInterface $insuranceAdjusterRepositoryInterface, BaseController $baseController)
    {
        $this->insuranceAdjusterRepositoryInterface = $insuranceAdjusterRepositoryInterface;
        $this->baseController = $baseController;
    }

    public function all()
    {
        try {
            $this->userId = Auth::id();
            $this->cacheKey = 'insurance_adjusters_' . $this->userId . '_total_list';

            // Refrescar la caché si es necesario
            $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () {
                return $this->insuranceAdjusterRepositoryInterface->index();
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
            Log::error('Database error occurred while fetching insurance adjusters: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => $this->userId
            ]);
            throw $e;

        } catch (\Exception $e) {
            Log::error('Error occurred while fetching insurance adjusters: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => $this->userId
            ]);
            throw $e;
        }
    }

    public function updateInsuranceAdjuster(array $updateDetails, string $uuid)
{
    DB::beginTransaction();

    try {
        $adjuster = $this->insuranceAdjusterRepositoryInterface->getByUuid($uuid);

        if (!$adjuster) {
            throw new \Exception('Insurance adjuster not found');
        }

         $existingAdjuster = $this->insuranceAdjusterRepositoryInterface->getByUserIdAndCompanyIdExceptCurrent(
            $updateDetails['user_id'],
            $updateDetails['insurance_company_id'],
            $uuid
        );

        if ($existingAdjuster) {
            throw new \Exception('The user is already assigned to another company');
        }

        // Actualizar el ajustador
        $adjuster = $this->insuranceAdjusterRepositoryInterface->update($updateDetails, $uuid);

        // Actualizar caché
        $this->updateInsuranceAdjustersCache();

        DB::commit();

        return $adjuster;
    } catch (\Exception $ex) {
        DB::rollBack();
        throw new \Exception('Error occurred while updating insurance adjuster: ' . $ex->getMessage());
    }
}


    private function updateInsuranceAdjustersCache()
    {
        $this->cacheKey = 'insurance_adjusters_total_list';

        if (!empty($this->cacheKey)) {
            $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () {
                return InsuranceAdjuster::orderBy('id', 'DESC')->get();
            });
        } else {
            throw new \Exception('Invalid cacheKey provided');
        }
    }

    public function storeInsuranceAdjuster(array $details)
    {
        DB::beginTransaction();

        try {
            $details['uuid'] = Uuid::uuid4()->toString();

             // Verificar si ya existe una entrada exactamente igual
        $existingAdjuster = $this->insuranceAdjusterRepositoryInterface->getByUserIdAndCompanyId(
            $details['user_id'],
            $details['insurance_company_id']
        );

        if ($existingAdjuster) {
            throw new \Exception('This user is already assigned to this company');
        }

            $adjuster = $this->insuranceAdjusterRepositoryInterface->store($details);

            $this->updateInsuranceAdjustersCache();

            DB::commit();

            return $adjuster;
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception('Error occurred while creating insurance adjuster: ' . $ex->getMessage());
        }
    }

    public function showInsuranceAdjuster(string $uuid)
    {
        try {
            $cacheKey = 'insurance_adjuster_' . $uuid;

            $adjuster = $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($uuid) {
                return $this->insuranceAdjusterRepositoryInterface->getByUuid($uuid);
            });

            return $adjuster;

        } catch (\Exception $ex) {
            throw new \Exception('Error occurred while retrieving insurance adjuster: ' . $ex->getMessage());
        }
    }

    public function deleteInsuranceAdjuster(string $uuid)
    {
        try {
            $adjuster = $this->insuranceAdjusterRepositoryInterface->delete($uuid);

            $this->baseController->invalidateCache('insurance_adjuster_' . $uuid);

            $this->updateInsuranceAdjustersCache();

            return $adjuster;
        } catch (\Exception $ex) {
            throw new \Exception('Error occurred while deleting insurance adjuster: ' . $ex->getMessage());
        }
    }
}