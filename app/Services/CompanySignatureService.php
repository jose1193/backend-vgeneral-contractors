<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\CompanySignatureRepositoryInterface;
use App\Interfaces\S3ServiceInterface;
use App\Interfaces\TransactionServiceInterface;
use App\Interfaces\CacheServiceInterface;
use App\DTOs\CompanySignatureDTO;
use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;
use Illuminate\Support\Str;

class CompanySignatureService
{
    private const CACHE_TIME = 720; // minutes
    private const CACHE_KEY_LIST = 'company_signatures_user_list_';
    private const SIGNATURE_S3_PATH = 'public/company_signatures';

    public function __construct(
        private readonly CompanySignatureRepositoryInterface $repository,
        private readonly S3Service $s3Service,
        private readonly TransactionService $transactionService,
        private readonly CacheService $cacheService,
        private readonly LoggerInterface $logger
    ) {}

    public function all(): Collection
    {   
       $cacheKey = $this->generateCacheKey(self::CACHE_KEY_LIST, (string) Auth::id());
        return $this->cacheService->getCachedData($cacheKey, self::CACHE_TIME, fn() => $this->repository->index());
    }

    public function storeData(CompanySignatureDTO $dto): object
    {
        return $this->transactionService->handleTransaction(function () use ($dto) {
            $this->validateNoExistingSignature();
            $details = $this->prepareSignatureDetails($dto);
            $signatureUrl = $this->storeSignatureInS3($details['signature_path']);
            $details['signature_path'] = $signatureUrl;
            $signature = $this->repository->store($details);
            $this->updateDataCache();
            //$this->logger->info('Company signature stored successfully', ['uuid' => $signature->uuid]);
            return $signature;
        }, 'storing company signature');
    }

    public function updateData(CompanySignatureDTO $dto, UuidInterface $uuid): ?object
    {
        return $this->transactionService->handleTransaction(function () use ($dto, $uuid) {
            $existingSignature = $this->getExistingSignature($uuid);
            $this->validateUserPermission($existingSignature);

            $updateDetails = $this->prepareUpdateDetails($dto, $uuid, $existingSignature);
            $signature = $this->repository->update($updateDetails, $uuid->toString());

            $this->updateCache($uuid, $signature);
            $this->updateDataCache();
            
            //$this->logger->info('Company signature updated successfully', ['uuid' => $uuid->toString()]);
            return $signature;
        }, 'updating company signature');
    }

    public function showData(UuidInterface $uuid): ?object
    {
        $cacheKey = $this->generateCacheKey('company_signature_', $uuid->toString());
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

            //$this->logger->info('Company signature deleted successfully', ['uuid' => $uuid->toString()]);
            return true;
        }, 'deleting company signature');
    }

    public function restoreSignature(UuidInterface $uuid): object
    {
        return $this->transactionService->handleTransaction(function () use ($uuid) {
            $signature = $this->repository->restore($uuid->toString());
            $this->invalidateCache($uuid);
            $this->updateDataCache();
            $this->logger->info('Company signature restored successfully', ['uuid' => $uuid->toString()]);
            return $signature;
        }, 'restoring company signature');
    }

    private function validateNoExistingSignature(): void
    {
        if ($this->repository->findFirst()) {
            throw new Exception('A company signature already exists.');
        }
    }

    private function prepareSignatureDetails(CompanySignatureDTO $dto): array
    {
        return [
            ...$dto->toArray(),
            'uuid' => Uuid::uuid4()->toString(),
            'user_id' => Auth::id(),
        ];
    }

    private function storeSignatureInS3(string $signaturePath): string
    {
        try {
            return $this->s3Service->storeSignatureInS3($signaturePath, self::SIGNATURE_S3_PATH);
        } catch (\Exception $e) {
            $this->logger->error('Failed to store signature in S3', ['error' => $e->getMessage()]);
            throw $e;
        }
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
        if ($signature->user_id !== Auth::id()) {
            throw new Exception("No permission to perform this operation.");
        }
    }

        private function prepareUpdateDetails(CompanySignatureDTO $dto, UuidInterface $uuid, object $existingSignature): array
    {
        $updateDetails = [
        'uuid' => $uuid->toString(),
        'user_id' => Auth::id(),
        ];

        $fields = ['companyName', 'phone', 'email', 'address', 'website'];
        foreach ($fields as $field) {
        if ($dto->$field !== null) {
            $updateDetails[Str::snake($field)] = $dto->$field;
        }
        }

        if ($dto->signaturePath !== null) {
        $updateDetails['signature_path'] = $this->handleSignaturePathUpdate($dto->signaturePath, $existingSignature);
        }

        return $updateDetails;
    }


    private function handleSignaturePathUpdate(string $newSignaturePath, object $existingSignature): string
    {
        if ($this->isValidUrl($newSignaturePath)) {
            return $existingSignature->signature_path;
        }
        return $this->replaceSignatureInS3($existingSignature, $newSignaturePath);
    }

    private function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    private function replaceSignatureInS3(object $existingSignature, string $newSignatureData): string
    {
        try {
            if (isset($existingSignature->signature_path)) {
                $this->s3Service->deleteFileFromStorage($existingSignature->signature_path);
            }
            return $this->s3Service->storeSignatureInS3($newSignatureData, self::SIGNATURE_S3_PATH);
        } catch (\Exception $e) {
            $this->logger->error('Failed to replace signature in S3', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function deleteSignatureFromS3(object $signature): void
    {
        if (isset($signature->signature_path)) {
            try {
                $this->s3Service->deleteFileFromStorage($signature->signature_path);
            } catch (\Exception $e) {
                $this->logger->error('Failed to delete signature from S3', ['error' => $e->getMessage()]);
                // Consider whether to throw or just log the error
            }
        }
    }

    private function updateCache(UuidInterface $uuid, object $signature): void
    {
        $cacheKey = $this->generateCacheKey('company_signature_', $uuid->toString());
        $this->cacheService->refreshCache($cacheKey, self::CACHE_TIME, fn() => $signature);
    }

    private function updateDataCache(): void
    {
        $cacheKey = $this->generateCacheKey(self::CACHE_KEY_LIST, (string) Auth::id());

        $this->cacheService->updateDataCache($cacheKey, self::CACHE_TIME, fn() => $this->repository->index());
    }

    private function invalidateCache(UuidInterface $uuid): void
    {
        $this->cacheService->invalidateCache($this->generateCacheKey('company_signature_', $uuid->toString()));
    }

    private function generateCacheKey(string $prefix, string $suffix): string
    {
        return $prefix . $suffix;
    }
}