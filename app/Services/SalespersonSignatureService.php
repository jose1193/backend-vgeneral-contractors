<?php

namespace App\Services;

use App\Interfaces\SalespersonSignatureRepositoryInterface;
use App\Http\Controllers\BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Exception;
use Illuminate\Database\QueryException;
use App\Helpers\ImageHelper;

class SalespersonSignatureService
{
    protected $signatureRepository;
    protected $baseController;
    protected $cacheTime = 720; // Cache time in minutes

    public function __construct(
        SalespersonSignatureRepositoryInterface $signatureRepository,
        BaseController $baseController
    ) {
        $this->signatureRepository = $signatureRepository;
        $this->baseController = $baseController;
    }

    private function getUserId()
    {
        return Auth::id();
    }

    public function all()
    {
        $userId = $this->getUserId();
        $cacheKey = 'salesperson_signatures_total_list_' . $userId;

        return $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () {
            return $this->signatureRepository->getSignaturesByUser(Auth::user());
        });
    }

    public function storeData(array $details)
    {
        return $this->handleTransaction(function () use ($details) {
            $userId = $this->getUserId();

            try {
                $signatureUrl = $this->storeSignatureInS3($details['signature_path']);
                $userIdRefBy = $details['user_id_ref_by'] ?? $userId;

                $signature = $this->signatureRepository->store([
                    'uuid' => Uuid::uuid4()->toString(),
                    'seller_id' => $details['seller_id'],
                    'signature_path' => $signatureUrl,
                    'user_id_ref_by' => $userIdRefBy
                ]);

                $this->updateDataCache();
                return $signature;

            } catch (QueryException $e) {
                $this->handleException($e, 'storing signature');
            } catch (Exception $e) {
                $this->handleException($e, 'storing signature');
            }
        });
    }

    public function updateData(array $updateDetails, string $uuid)
    {
        return $this->handleTransaction(function () use ($updateDetails, $uuid) {
            $userId = $this->getUserId();

            try {
                $existingSignature = $this->signatureRepository->getByUuid($uuid);

                if (!$existingSignature) {
                    throw new Exception('Signature not found.');
                }

                if (isset($updateDetails['signature_path'])) {
                    $updateDetails['signature_path'] = $this->replaceSignatureInS3($existingSignature, $updateDetails['signature_path']);
                } else {
                    $updateDetails['signature_path'] = $existingSignature->signature_path;
                }

                $updateDetails['user_id_ref_by'] = $updateDetails['user_id_ref_by'] ?? $userId;

                $updatedSignature = $this->signatureRepository->update($updateDetails, $uuid);

                $this->updateDataCache();
                return $updatedSignature;

            } catch (QueryException $e) {
                $this->handleException($e, 'updating signature');
            } catch (Exception $e) {
                $this->handleException($e, 'updating signature');
            }
        });
    }

    public function showData(string $uuid)
    {
        $cacheKey = 'salesperson_signature_' . $uuid;

        return $this->baseController->getCachedData($cacheKey, $this->cacheTime, function () use ($uuid) {
            try {
                return $this->signatureRepository->getByUuid($uuid);
            } catch (QueryException $e) {
                $this->handleException($e, 'fetching signature');
            } catch (Exception $e) {
                $this->handleException($e, 'fetching signature');
            }
            return null;
        });
    }

    public function deleteData(string $uuid)
    {
        return $this->handleTransaction(function () use ($uuid) {
            try {
                $existingSignature = $this->signatureRepository->getByUuid($uuid);

                if (!$existingSignature) {
                    throw new Exception('Signature not found.');
                }

                $this->deleteSignatureFromS3($existingSignature->signature_path);
                $this->signatureRepository->delete($uuid);

                $this->baseController->invalidateCache('salesperson_signature_' . $uuid);
                $this->updateDataCache();

            } catch (QueryException $e) {
                $this->handleException($e, 'deleting signature');
            } catch (Exception $e) {
                $this->handleException($e, 'deleting signature');
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

    private function storeSignatureInS3(string $signatureData): string
    {
        try {
            return ImageHelper::storeSignatureInS3($signatureData, 'public/salesperson_signatures');
        } catch (Exception $e) {
            $this->handleException($e, 'storing signature in S3');
        }
    }

    private function replaceSignatureInS3($existingSignature, string $newSignatureData): string
    {
        try {
            if ($existingSignature && isset($existingSignature->signature_path)) {
                $this->deleteSignatureFromS3($existingSignature->signature_path);
            }
            return $this->storeSignatureInS3($newSignatureData);
        } catch (Exception $e) {
            $this->handleException($e, 'replacing signature in S3');
        }
    }

    private function deleteSignatureFromS3(string $signatureUrl)
    {
        try {
            if (!empty($signatureUrl)) {
                ImageHelper::deleteFileFromStorage($signatureUrl);
            }
        } catch (Exception $e) {
            $this->handleException($e, 'deleting signature from S3');
        }
    }

    private function updateDataCache()
    {
        //$userId = Auth::id();
        $userId = $this->getUserId();
        $cacheKey = 'salesperson_signatures_total_list_' . $userId;

        try {
            $this->baseController->refreshCache($cacheKey, $this->cacheTime, function () {
                return $this->signatureRepository->index();
            });
        } catch (QueryException $e) {
            $this->handleException($e, 'updating cache');
        } catch (Exception $e) {
            $this->handleException($e, 'updating cache');
        }
    }
}
