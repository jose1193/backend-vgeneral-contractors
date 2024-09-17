<?php // app/Services/AllianceCompanyService.php

namespace App\Services;

use App\Interfaces\AllianceCompanyRepositoryInterface;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Exception;
use Illuminate\Database\QueryException;

class AllianceCompanyService
{
    protected $baseController;
    protected $allianceCompanyRepositoryInterface;
    protected $cacheKey;
    protected $cacheTime = 720;

    public function __construct(
        AllianceCompanyRepositoryInterface $allianceCompanyRepositoryInterface,
        BaseController $baseController
    ) {
        $this->allianceCompanyRepositoryInterface = $allianceCompanyRepositoryInterface;
        $this->baseController = $baseController;
    }

    public function all()
    {
        try {
            $this->cacheKey = 'alliance_companies_total_list';

            $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () {
                return $this->allianceCompanyRepositoryInterface->index();
            });

            $data = Cache::get($this->cacheKey);

            if ($data === null || !is_iterable($data)) {
                Log::warning('Data fetched from cache is null or not iterable');
                return null; 
            }

            return $data;
        } catch (QueryException $e) {
            Log::error('Database error occurred while fetching alliance companies: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e; 
        } catch (Exception $e) {
            Log::error('Error occurred while fetching alliance companies: ' . $e->getMessage(), [
                'exception' => $e
            ]);
            throw $e; 
        }
    }

    public function storeData(array $details)
    {
        DB::beginTransaction();

        try {
            $this->validateUniqueCompany($details['company_name']);

            $details['uuid'] = Uuid::uuid4()->toString();
            $details['user_id'] = Auth::id();

            $data = $this->allianceCompanyRepositoryInterface->store($details);

            $this->updateDataCache();
            DB::commit();

            return $data;
        } catch (Exception $ex) {
            DB::rollBack();
            throw new Exception('Error occurred while storing alliance company: ' . $ex->getMessage());
        }
    }

    public function updateData(array $updateDetails, string $uuid)
    {
        DB::beginTransaction();

        try {
            $existingCompany = $this->allianceCompanyRepositoryInterface->getByUuid($uuid);

            if (!$existingCompany || $existingCompany->user_id !== Auth::id()) {
                throw new Exception('No permission to update this company or company not found.');
            }

            $data = $this->allianceCompanyRepositoryInterface->update($updateDetails, $uuid);

            $this->updateDataCache();
            DB::commit();

            return $data;
        } catch (Exception $ex) {
            DB::rollBack();
            throw new Exception('Error occurred while updating alliance company: ' . $ex->getMessage());
        }
    }

    public function showData(string $uuid)
    {
        try {
            $cacheKey = 'alliance_company_' . $uuid;

            $data = $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($uuid) {
                return $this->allianceCompanyRepositoryInterface->getByUuid($uuid);
            });

            return $data;
        } catch (Exception $ex) {
            throw new Exception('Error occurred while retrieving alliance company: ' . $ex->getMessage());
        }
    }

    public function deleteData(string $uuid)
    {
        DB::beginTransaction();

        try {
            $existingCompany = $this->allianceCompanyRepositoryInterface->getByUuid($uuid);

            if (!$existingCompany || $existingCompany->user_id !== Auth::id()) {
                throw new Exception('No permission to delete this company or company not found.');
            }

            $data = $this->allianceCompanyRepositoryInterface->delete($uuid);

            $this->baseController->invalidateCache('alliance_company_' . $uuid);
            $this->updateDataCache();
            DB::commit();

            return $data;
        } catch (Exception $ex) {
            DB::rollBack();
            throw new Exception('Error occurred while deleting alliance company: ' . $ex->getMessage());
        }
    }

    private function updateDataCache()
    {
        $this->cacheKey = 'alliance_companies_total_list';

        if (!empty($this->cacheKey)) {
            $this->baseController->refreshCache($this->cacheKey, $this->cacheTime, function () {
                return $this->allianceCompanyRepositoryInterface->index();
            });
        } else {
            throw new Exception('Invalid cacheKey provided');
        }
    }

    private function validateUniqueCompany(string $companyName)
    {
        $existingCompany = $this->allianceCompanyRepositoryInterface->findByCompanyName($companyName);

        if ($existingCompany) {
            throw new Exception('A company with this name already exists.');
        }
    }
}
