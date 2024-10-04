<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\SalespersonSignatureRepositoryInterface;
use App\Interfaces\S3ServiceInterface;
use App\Interfaces\TransactionServiceInterface;
use App\Interfaces\CacheServiceInterface;
use App\DTOs\SalespersonSignatureDTO;
use Exception;
use App\Models\SalespersonSignature;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

class SalespersonSignatureService
{
    private const CACHE_TIME = 720; // minutes
    private const CACHE_KEY_LIST = 'salesperson_signatures_user_list_';
    private const SIGNATURE_S3_PATH = 'public/salesperson_signatures';

    public function __construct(
        private readonly SalespersonSignatureRepositoryInterface $repository,
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

    public function storeData(SalespersonSignatureDTO $dto): SalespersonSignature
    {
        return $this->transactionService->handleTransaction(function () use ($dto) {
            $user = Auth::user();
            if (!$user) {
                throw new Exception("No authenticated user found");
            }

            $salespersonId = $this->getSalespersonId($user, $dto->salesPersonId);

            if ($this->repository->getBySalespersonId($salespersonId)) {
                throw new Exception("A signature already exists for this salesperson. Use updateData to modify it.");
            }

            $details = $this->prepareSignatureDetails($dto, $salespersonId);
            $signatureUrl = $this->s3Service->storeSignatureInS3($details['signature_path'], self::SIGNATURE_S3_PATH);
            $details['signature_path'] = $signatureUrl;
            $signature = $this->repository->store($details);

            $this->updateDataCache($salespersonId);
            $this->logger->info('Salesperson signature stored successfully', ['uuid' => $signature->uuid]);
            return $signature;
        }, 'storing salesperson signature');
    }

    public function updateData(SalespersonSignatureDTO $dto, UuidInterface $uuid): SalespersonSignature
    {
        return $this->transactionService->handleTransaction(function () use ($dto, $uuid) {
            $user = Auth::user();
            if (!$user) {
                throw new Exception("No authenticated user found");
            }

            $existingSignature = $this->getExistingSignature($uuid);

            $this->validateUpdatePermission($user, $existingSignature, $dto);

            $updateDetails = $this->prepareUpdateDetails($dto, $uuid, $existingSignature);
            $signature = $this->repository->update($updateDetails, $uuid->toString());

            $this->updateCache($uuid, $signature);
            $this->updateDataCache($signature->salesperson_id);
            
            $this->logger->info('Salesperson signature updated successfully', ['uuid' => $uuid->toString()]);
            return $signature;
        }, 'updating salesperson signature');
    }

    public function showData(UuidInterface $uuid): SalespersonSignature
    {
        $cacheKey = $this->generateCacheKey('salesperson_signature_', $uuid->toString());
        return $this->cacheService->getCachedData($cacheKey, self::CACHE_TIME, fn() => $this->repository->getByUuid($uuid->toString()));
    }

    public function deleteData(UuidInterface $uuid): bool
    {
        return $this->transactionService->handleTransaction(function () use ($uuid) {
            $existingSignature = $this->getExistingSignature($uuid);
            $this->validateSuperAdminPermission();

            $salespersonId = $existingSignature->salesperson_id;

            $this->repository->delete($uuid->toString());
            $this->deleteSignatureFromS3($existingSignature);

            $this->invalidateCache($uuid);
            $this->updateDataCache($salespersonId);

            $this->logger->info('Salesperson signature deleted successfully', ['uuid' => $uuid->toString()]);
            return true;
        }, 'deleting salesperson signature');
    }

    private function getSalespersonId($user, ?int $salesPersonId): int
    {
        if ($user->hasRole('Salesperson')) {
            return $user->id;
        } elseif ($user->hasRole('Super Admin') && $salesPersonId !== null) {
            return $salesPersonId;
        } else {
            throw new Exception("Unauthorized or invalid salesperson ID");
        }
    }

    private function validateUpdatePermission($user, SalespersonSignature $signature, SalespersonSignatureDTO $dto): void
    {
        if ($user->hasRole('Super Admin')) {
            if ($dto->salesPersonId !== null) {
                $signature->salesperson_id = $dto->salesPersonId;
            }
        } elseif ($user->hasRole('Salesperson')) {
            if ($signature->salesperson_id !== $user->id) {
                throw new Exception("You don't have permission to update this signature.");
            }
        } else {
            throw new Exception("You don't have permission to update signatures.");
        }
    }

    private function prepareSignatureDetails(SalespersonSignatureDTO $dto, int $salespersonId): array
    {
        return [
            ...$dto->toArray(),
            'uuid' => Uuid::uuid4()->toString(),
            'user_id_ref_by' => Auth::id(),
            'salesperson_id' => $salespersonId,
        ];
    }

    private function getExistingSignature(UuidInterface $uuid): SalespersonSignature
    {
        $signature = $this->repository->getByUuid($uuid->toString());
        if (!$signature) {
            throw new Exception("Signature not found");
        }
        return $signature;
    }

    private function validateSuperAdminPermission(): void
    {
        $user = Auth::user();
        if (!$user || !$user->hasRole('Super Admin')) {
            throw new Exception("Unauthorized access. Only super admins can perform this operation.");
        }
    }

    private function prepareUpdateDetails(SalespersonSignatureDTO $dto, UuidInterface $uuid, SalespersonSignature $existingSignature): array
    {
        $updateDetails = [
            'uuid' => $uuid->toString(),
            'user_id_ref_by' => Auth::id(),
        ];

        if (Auth::user()->hasRole('Super Admin') && $dto->salesPersonId !== null) {
            $updateDetails['salesperson_id'] = $dto->salesPersonId;
        }

        if ($dto->signaturePath !== null) {
            $updateDetails['signature_path'] = $this->handleSignaturePathUpdate($dto->signaturePath, $existingSignature);
        }

        return $updateDetails;
    }

    private function handleSignaturePathUpdate(string $newSignaturePath, SalespersonSignature $existingSignature): string
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

    private function replaceSignatureInS3(SalespersonSignature $existingSignature, string $newSignatureData): string
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

    private function deleteSignatureFromS3(SalespersonSignature $signature): void
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

    private function updateCache(UuidInterface $uuid, SalespersonSignature $signature): void
    {
        $cacheKey = $this->generateCacheKey('salesperson_signature_', $uuid->toString());
        $this->cacheService->refreshCache($cacheKey, self::CACHE_TIME, fn() => $signature);
    }

    private function updateDataCache(int $salespersonId): void
    {
        // Invalidar la caché del vendedor afectado
        $salespersonCacheKey = $this->generateCacheKey(self::CACHE_KEY_LIST, (string) $salespersonId);
        $this->cacheService->forget($salespersonCacheKey);

        // Invalidar la caché de todos los Super Admins
        $superAdmins = $this->repository->getAllSuperAdmins();
        foreach ($superAdmins as $admin) {
            $adminCacheKey = $this->generateCacheKey(self::CACHE_KEY_LIST, (string) $admin->id);
            $this->cacheService->forget($adminCacheKey);
        }
    }

    

    private function invalidateCache(UuidInterface $uuid): void
    {
        $this->cacheService->invalidateCache($this->generateCacheKey('salesperson_signature_', $uuid->toString()));
    }

    private function generateCacheKey(string $prefix, string $suffix): string
    {
        return $prefix . $suffix;
    }
}