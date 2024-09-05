<?php

namespace App\Services;

use App\Http\Controllers\BaseController;
use App\Interfaces\ServiceRequestRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Exception;

class ServiceRequestService
{
    protected $serviceData;
    protected $baseController;
    protected $cacheTime = 720; // Cache duration in minutes

    public function __construct(
        ServiceRequestRepositoryInterface $serviceData,
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
        $cacheKey = 'service_requests_total_list_' . $userId;

        return $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($userId) {
            return $this->serviceData->index();
        });
    }

    public function storeData(array $details)
    {
        return $this->handleTransaction(function () use ($details) {
            try {
                // Generate a UUID for the new record
                $details['uuid'] = Uuid::uuid4()->toString();

                // Store the ServiceRequest information in the database
                $serviceRequest = $this->serviceData->store($details);

                $this->updateDataCache();
                return $serviceRequest;

            } catch (Exception $e) {
                $this->handleException($e, 'storing service request');
            }
        });
    }

    public function updateData(array $updateDetails, string $uuid)
    {
        return $this->handleTransaction(function () use ($updateDetails, $uuid) {
            try {
                $existingServiceRequest = $this->serviceData->getByUuid($uuid);

                // Update the service request information in the database
                $updatedServiceRequest = $this->serviceData->update($updateDetails, $uuid);

                $this->updateDataCache();
                return $updatedServiceRequest;

            } catch (Exception $e) {
                $this->handleException($e, 'updating service request');
            }
        });
    }

    public function showData(string $uuid)
    {
        $cacheKey = 'service_request_' . $uuid;

        return $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($uuid) {
            try {
                return $this->serviceData->getByUuid($uuid);
            } catch (Exception $e) {
                $this->handleException($e, 'fetching service request');
                return null;
            }
        });
    }

    public function deleteData(string $uuid)
    {
        return $this->handleTransaction(function () use ($uuid) {
            try {
                $cacheKey = 'service_request_' . $uuid;
                $existingServiceRequest = $this->serviceData->getByUuid($uuid);

                if (!$existingServiceRequest) {
                    throw new Exception("Service request not found");
                }

                // Delete the record from the database
                $this->serviceData->delete($uuid);

                // Invalidate the cache
                $this->baseController->invalidateCache($cacheKey);
                $this->updateDataCache();

            } catch (Exception $e) {
                $this->handleException($e, 'deleting service request');
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
        $cacheKey = 'service_requests_total_list_' . $userId;

        if (!empty($cacheKey)) {
            $this->baseController->refreshCache($cacheKey, $this->cacheTime, function () {
                return $this->serviceData->index();
            });
        } else {
            throw new \Exception('Invalid cacheKey provided');
        }
    }
}