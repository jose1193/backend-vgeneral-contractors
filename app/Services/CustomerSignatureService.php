<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\CustomerSignatureRepositoryInterface;
use App\Interfaces\S3ServiceInterface;
use App\Interfaces\TransactionServiceInterface;
use App\Interfaces\CacheServiceInterface;
use App\DTOs\CustomerSignatureDTO;
use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;
use Illuminate\Support\Str;

class CustomerSignatureService
{
    private const CACHE_TIME = 720; // minutes
    private const CACHE_KEY_LIST = 'customer_signatures_user_list_';
    private const SIGNATURE_S3_PATH = 'public/customer_signatures';

    public function __construct(
        private readonly CustomerSignatureRepositoryInterface $repository,
        private readonly S3Service $s3Service,
        private readonly TransactionService $transactionService,
        private readonly CacheService $cacheService,
        private readonly LoggerInterface $logger
    ) {}

    public function all(): Collection
    {   
        $cacheKey = $this->generateCacheKey(self::CACHE_KEY_LIST, (string) Auth::id());
        return $this->cacheService->getCachedData($cacheKey, self::CACHE_TIME, fn() => $this->repository->getSignaturesByUser(Auth::user()));
    }

    public function storeData(CustomerSignatureDTO $dto): object
    {
        return $this->transactionService->handleTransaction(function () use ($dto) {
            $details = $this->prepareSignatureDetails($dto);
            $signatureUrl = $this->storeSignatureInS3($details['signature_data']);
            $details['signature_data'] = $signatureUrl;
            $signature = $this->repository->store($details);
            $this->updateDataCache();
            return $signature;
        }, 'storing customer signature');
    }

    public function updateData(CustomerSignatureDTO $dto, UuidInterface $uuid): ?object
    {
        return $this->transactionService->handleTransaction(function () use ($dto, $uuid) {
            $existingSignature = $this->getExistingSignature($uuid);
            $this->validateUserPermission($existingSignature);

            $updateDetails = $this->prepareUpdateDetails($dto, $uuid, $existingSignature);
            $signature = $this->repository->update($updateDetails, $uuid->toString());

            $this->updateCache($uuid, $signature);
            $this->updateDataCache();
            return $signature;
        }, 'updating customer signature');
    }

    public function showData(UuidInterface $uuid): ?object
    {
        $cacheKey = $this->generateCacheKey('customer_signature_', $uuid->toString());
        return $this->cacheService->getCachedData($cacheKey, self::CACHE_TIME, fn() => $this->repository->getByUuid($uuid->toString()));
    }

    public function deleteData(UuidInterface $uuid): bool
    {
        return $this->transactionService->handleTransaction(function () use ($uuid) {
            $existingSignature = $this->getExistingSignature($uuid);
            $this->validateUserPermission($existingSignature);

            $this->repository->delete($uuid->toString());
            $this->deleteSignatureFromS3($existingSignature);

            $this->invalidateCache($uuid);
            $this->updateDataCache();
            
            return true;
        }, 'deleting customer signature');
    }

    private function prepareSignatureDetails(CustomerSignatureDTO $dto): array
    {
        return [
            ...$dto->toArray(),
            'uuid' => Uuid::uuid4()->toString(),
            'user_id_ref_by' => $dto->userIdRefBy ?? Auth::id(),
        ];
    }

    private function storeSignatureInS3(string $signatureData): string
    {
        return $this->s3Service->storeSignatureInS3($signatureData, self::SIGNATURE_S3_PATH);
    }

    private function getExistingSignature(UuidInterface $uuid): object
    {
        $signature = $this->repository->getByUuid($uuid->toString());
        if (!$signature) {
            throw new Exception("Signature not found");
        }
        return $signature;
    }

    private function validateUserPermission(object $signature): void
    {
        if ($signature->user_id_ref_by !== Auth::id()) {
            throw new Exception("No permission to perform this operation.");
        }
    }

    private function prepareUpdateDetails(CustomerSignatureDTO $dto, UuidInterface $uuid, object $existingSignature): array
    {
        $updateDetails = [
            'uuid' => $uuid->toString(),
            'user_id_ref_by' => $dto->userIdRefBy ?? Auth::id(),
        ];

        $fields = ['customerId', 'signatureData'];
        foreach ($fields as $field) {
            if ($dto->$field !== null) {
                $updateDetails[Str::snake($field)] = $dto->$field;
            }
        }

        if ($dto->signatureData !== null) {
            $updateDetails['signature_data'] = $this->handleSignatureDataUpdate($dto->signatureData, $existingSignature);
        }

        return $updateDetails;
    }

    private function handleSignatureDataUpdate(string $newSignatureData, object $existingSignature): string
    {
        if ($this->isValidUrl($newSignatureData)) {
            return $existingSignature->signature_data;
        }
        return $this->replaceSignatureInS3($existingSignature, $newSignatureData);
    }

    private function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    private function replaceSignatureInS3(object $existingSignature, string $newSignatureData): string
    {
        if (isset($existingSignature->signature_data)) {
            $this->s3Service->deleteFileFromStorage($existingSignature->signature_data);
        }
        return $this->s3Service->storeSignatureInS3($newSignatureData, self::SIGNATURE_S3_PATH);
    }

    private function deleteSignatureFromS3(object $signature): void
    {
        if (isset($signature->signature_data)) {
            $this->s3Service->deleteFileFromStorage($signature->signature_data);
        }
    }

    private function updateCache(UuidInterface $uuid, object $signature): void
    {
        $cacheKey = $this->generateCacheKey('customer_signature_', $uuid->toString());
        $this->cacheService->refreshCache($cacheKey, self::CACHE_TIME, fn() => $signature);
    }

    private function updateDataCache(): void
    {
        $cacheKey = $this->generateCacheKey(self::CACHE_KEY_LIST, (string) Auth::id());
        $this->cacheService->updateDataCache($cacheKey, self::CACHE_TIME, fn() => $this->repository->getSignaturesByUser(Auth::user()));
    }

    private function invalidateCache(UuidInterface $uuid): void
    {
        $this->cacheService->invalidateCache($this->generateCacheKey('customer_signature_', $uuid->toString()));
    }

    private function generateCacheKey(string $prefix, string $suffix): string
    {
        return $prefix . $suffix;
    }
}