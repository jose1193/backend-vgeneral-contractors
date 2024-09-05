<?php

namespace App\Services;

use App\Http\Controllers\BaseController;
use App\Interfaces\ScopeSheetRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Exception;

class ScopeSheetService
{
    protected $serviceData;
    protected $baseController;
    protected $cacheTime = 720; // Cache duration in minutes
    protected $cacheKeyList = 'claim_agreement_full_total_list_'; 


    public function __construct(
        ScopeSheetRepositoryInterface $serviceData,
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
        $cacheKey = $this->cacheKeyList . $userId;

        return $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($userId) {
            return $this->serviceData->getByUser(Auth::user());
        });
    }

    public function storeData(array $details)
    {
        return $this->handleTransaction(function () use ($details) {
            try {
                // Check if a ScopeSheet with the same claim_id already exists
                $existingScope = $this->serviceData->findExistingScope($details['claim_id']);

                if ($existingScope) {
                    // If it exists, throw an exception with a custom message
                    throw new Exception('A ScopeSheet with the given claim_id already exists.');
                }

                // Generate a UUID for the new record
                $details['uuid'] = Uuid::uuid4()->toString();
                $details['generated_by'] = $this->getUserId();

                // Store the ScopeSheet information in the database
                $scopeSheet = $this->serviceData->store($details);

                $this->updateDataCache();
                return $scopeSheet;

            } catch (Exception $e) {
                // Handle the exception using the handleException method
                $this->handleException($e, 'storing scope sheet');
            }
        });
    }

    public function updateData(array $updateDetails, string $uuid)
    {
        return $this->handleTransaction(function () use ($updateDetails, $uuid) {
            try {
                $existingScopeSheet = $this->serviceData->getByUuid($uuid);

                // Update the scope sheet information in the database
                $updatedScopeSheet = $this->serviceData->update($updateDetails, $uuid);

                $this->updateDataCache();
                return $updatedScopeSheet;

            } catch (Exception $e) {
                $this->handleException($e, 'updating scope sheet');
            }
        });
    }

    public function showData(string $uuid)
    {
        $cacheKey = 'scope_sheet_' . $uuid;

        return $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($uuid) {
            try {
                return $this->serviceData->getByUuid($uuid);
            } catch (Exception $e) {
                $this->handleException($e, 'fetching scope sheet');
                return null;
            }
        });
    }

    public function deleteData(string $uuid)
    {
        return $this->handleTransaction(function () use ($uuid) {
            try {
                $cacheKey = 'scope_sheet_' . $uuid;
                $existingScopeSheet = $this->serviceData->getByUuid($uuid);

                if (!$existingScopeSheet) {
                    throw new \Exception("Scope sheet not found");
                }

                // Delete the scope sheet from the database
                $this->serviceData->delete($uuid);

                // Invalidate the cache
                $this->baseController->invalidateCache($cacheKey);
                $this->updateDataCache();

            } catch (Exception $e) {
                $this->handleException($e, 'deleting scope sheet');
                // Optionally rethrow the exception if you need to handle it further up the call stack
                throw $e;
            }
        });
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
        $cacheKey = $this->cacheKeyList . $userId;

        if (!empty($cacheKey)) {
            $this->baseController->refreshCache($cacheKey, $this->cacheTime, function () {
                return $this->serviceData->getByUser(Auth::user());
            });
        } else {
            throw new \Exception('Invalid cacheKey provided');
        }
    }
}
