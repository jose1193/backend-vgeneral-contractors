<?php // app/Services/InsuranceCompanyService.php
namespace App\Services;

use App\Interfaces\InsuranceCompanyRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\BaseController;
use App\Models\TypeDamage;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\QueryException;

class InsuranceCompanyService
{
    protected $baseController;
    protected $insuranceCompanyRepositoryInterface;
    protected $cacheKey;
    protected $cacheTime = 720;

    public function __construct(InsuranceCompanyRepositoryInterface $insuranceCompanyRepositoryInterface, BaseController $baseController)
    {
        $this->insuranceCompanyRepositoryInterface = $insuranceCompanyRepositoryInterface;
        $this->baseController = $baseController;
    }

    public function all()
    {
        try {
            $this->cacheKey = 'insurance_companies_total_list';

            $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () {
                return $this->insuranceCompanyRepositoryInterface->index();
            });

            $data = Cache::get($this->cacheKey);

            if ($data === null || !is_iterable($data)) {
                Log::warning('Data fetched from cache is null or not iterable');
                return null; 
            }

            return $data;
        } catch (QueryException $e) {
            Log::error('Database error occurred while fetching insurance companies: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e; 
        } catch (\Exception $e) {
            Log::error('Error occurred while fetching insurance companies: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e; 
        }
    }

    public function updateData(array $updateDetails, string $uuid)
    {
        DB::beginTransaction();

        try {
            $data = $this->insuranceCompanyRepositoryInterface->update($updateDetails, $uuid);

            $this->updateDataCache();
            DB::commit();

            return $data;
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception('Error occurred while updating insurance company: ' . $ex->getMessage());
        }
    }

    private function updateDataCache()
    {
        $this->cacheKey = 'insurance_companies_total_list';

        if (!empty($this->cacheKey)) {
            $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () {
                return $this->insuranceCompanyRepositoryInterface->index();
            });
        } else {
            throw new \Exception('Invalid cacheKey provided');
        }
    }

    public function storeData(array $details)
    {
        DB::beginTransaction();

        try {
            $details['uuid'] = Uuid::uuid4()->toString();

            $insuranceCompany = $this->insuranceCompanyRepositoryInterface->store($details);

            $this->updateDataCache();
            DB::commit();

            return $insuranceCompany;
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception('Error occurred while storing insurance company: ' . $ex->getMessage());
        }
    }

    public function showData(string $uuid)
    {
        try {
            $cacheKey = 'insurance_company_' . $uuid;

            // Obtiene los datos de la compañía de seguros desde la caché o desde la base de datos si no están en caché
            $insuranceCompany = $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($uuid) {
                return $this->insuranceCompanyRepositoryInterface->getByUuid($uuid);
            });

            return $insuranceCompany;
        } catch (\Exception $ex) {
            throw new \Exception('Error occurred while retrieving insurance company: ' . $ex->getMessage());
        }
    }

    public function deleteData(string $uuid)
    {
        try {
            $insuranceCompany = $this->insuranceCompanyRepositoryInterface->delete($uuid);

            // Invalidar el caché de la compañía de seguros
            $this->baseController->invalidateCache('insurance_company_' . $uuid);

            // Actualizar la caché de la lista de compañías de seguros
            $this->updateDataCache();

            return $insuranceCompany;
        } catch (\Exception $ex) {
            throw new \Exception('Error occurred while deleting insurance company: ' . $ex->getMessage());
        }
    }
}