<?php // app/Services/PublicCompanyService.php
namespace App\Services;

use App\Interfaces\PublicCompanyRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\BaseController;
use App\Models\PublicCompany;
use Ramsey\Uuid\Uuid;
use Illuminate\Database\QueryException;

class PublicCompanyService
{
    protected $baseController;
    protected $publicCompanyRepositoryInterface;
    protected $cacheKey;
    protected $cacheTime = 720;

     public function __construct(PublicCompanyRepositoryInterface $publicCompanyRepositoryInterface, BaseController $baseController)
    {
        $this->publicCompanyRepositoryInterface = $publicCompanyRepositoryInterface;
        $this->baseController = $baseController;
    }

    public function all()
    {
        try {
            $this->cacheKey = 'public_companies_total_list';

            $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () {
                return $this->publicCompanyRepositoryInterface->index();
            });

            $data = Cache::get($this->cacheKey);

            if ($data === null || !is_iterable($data)) {
                Log::warning('Data fetched from cache is null or not iterable');
                return null; 
            }

            return $data;
        } catch (QueryException $e) {
            Log::error('Database error occurred while fetching public companies: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e; 
        } catch (\Exception $e) {
            Log::error('Error occurred while fetching public companies: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e; 
        }
    }

    public function updateData(array $updateDetails, string $uuid)
    {
        DB::beginTransaction();

        try {
            $data = $this->publicCompanyRepositoryInterface->update($updateDetails, $uuid);

            $this->updateDataCache();
            DB::commit();

            return $data;
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception('Error occurred while updating public company: ' . $ex->getMessage());
        }
    }



    private function updateDataCache()
    {
        $this->cacheKey = 'public_companies_total_list';

        if (!empty($this->cacheKey)) {
            $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () {
                return $this->publicCompanyRepositoryInterface->index();
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

            $data = $this->publicCompanyRepositoryInterface->store($details);

            $this->updateDataCache();
            DB::commit();

            return $data;
        } catch (\Exception $ex) {
            DB::rollBack();
            throw new \Exception('Error occurred while storing public company: ' . $ex->getMessage());
        }
    }



    public function showData(string $uuid)
    {
        try {
            $cacheKey = 'public_company_' . $uuid;

            // Obtain the public company data from the cache or from the database if not cached
            $data = $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($uuid) {
                return $this->publicCompanyRepositoryInterface->getByUuid($uuid);
            });

            return $data;
        } catch (\Exception $ex) {
            throw new \Exception('Error occurred while retrieving public company: ' . $ex->getMessage());
        }
    }



    public function deleteData(string $uuid)
    {
        try {
            $data = $this->publicCompanyRepositoryInterface->delete($uuid);

            // Invalidate the cache for the public company
            $this->baseController->invalidateCache('public_company_' . $uuid);

            // Update the cache for the list of public companies
            $this->updateDataCache();

            return $data;
        } catch (\Exception $ex) {
            throw new \Exception('Error occurred while deleting public company: ' . $ex->getMessage());
        }
    }


    
}