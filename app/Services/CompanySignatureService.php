<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\CompanySignatureRepositoryInterface;
use App\Interfaces\S3ServiceInterface;
use App\Interfaces\TransactionServiceInterface;
use App\DTOs\CompanySignatureDTO;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\SignatureAlreadyExistsException;
use App\Exceptions\SignatureNotFoundException;
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
    private const CACHE_KEY_SIGNATURE = 'company_signature_';
    private const SIGNATURE_S3_PATH = 'public/company_signatures';

    public function __construct(
        private readonly CompanySignatureRepositoryInterface $repository,
        private readonly S3Service $s3Service,
        private readonly TransactionService $transactionService,
        private readonly UserCacheService $userCacheService,
        private readonly LoggerInterface $logger
    ) {}

    public function all(): Collection
    {   
        return $this->userCacheService->getCachedUserList(
            self::CACHE_KEY_LIST,
            Auth::id(),
            fn() => $this->repository->index()
        );
    }

    public function storeData(CompanySignatureDTO $dto): object
    {
        return $this->transactionService->handleTransaction(function () use ($dto) {
            $this->ensureSignatureDoesNotExist();
            $details = $this->prepareSignatureDetails($dto);
            $signature = $this->createSignature($details);
            
            $this->updateCaches(Auth::id(), Uuid::fromString($signature->uuid));
            $this->logger->info('Company signature stored successfully', ['uuid' => $signature->uuid]);
            return $signature;
        }, 'storing company signature');
    }

    public function updateData(CompanySignatureDTO $dto, UuidInterface $uuid): object
    {
        return $this->transactionService->handleTransaction(function () use ($dto, $uuid) {
            $existingSignature = $this->getExistingSignature($uuid);
            $this->validateUserPermission($existingSignature);

            $updateDetails = $this->prepareUpdateDetails($dto, $uuid, $existingSignature);
            $signature = $this->repository->update($updateDetails, $uuid->toString());

            $this->updateCaches($signature->user_id, $uuid);
            
            $this->logger->info('Company signature updated successfully', ['uuid' => $uuid->toString()]);
            return $signature;
        }, 'updating company signature');
    }

    public function showData(UuidInterface $uuid): ?object
    {
        return $this->userCacheService->getCachedItem(
            self::CACHE_KEY_SIGNATURE,
            $uuid->toString(),
            fn() => $this->repository->getByUuid($uuid->toString())
        );
    }

    public function deleteData(UuidInterface $uuid): bool
    {
        return $this->transactionService->handleTransaction(function () use ($uuid) {
            $existingSignature = $this->getExistingSignature($uuid);
            $this->validateUserPermission($existingSignature);

            $this->repository->delete($uuid->toString());
            $this->deleteSignatureFromS3($existingSignature);

            $this->updateCaches($existingSignature->user_id, $uuid);

            $this->logger->info('Company signature deleted successfully', ['uuid' => $uuid->toString()]);
            return true;
        }, 'deleting company signature');
    }

    public function restoreSignature(UuidInterface $uuid): object
    {
        return $this->transactionService->handleTransaction(function () use ($uuid) {
            $signature = $this->repository->restore($uuid->toString());
            $this->updateCaches($signature->user_id, $uuid);
            $this->logger->info('Company signature restored successfully', ['uuid' => $uuid->toString()]);
            return $signature;
        }, 'restoring company signature');
    }

    private function ensureSignatureDoesNotExist(): void
    {
        if ($this->repository->findFirst()) {
            throw new SignatureAlreadyExistsException("A company signature already exists. Use updateData to modify it.");
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

    private function createSignature(array $details): object
    {
        $signatureUrl = $this->s3Service->storeSignatureInS3($details['signature_path'], self::SIGNATURE_S3_PATH);
        $details['signature_path'] = $signatureUrl;
        return $this->repository->store($details);
    }

    private function getExistingSignature(UuidInterface $uuid): object
    {
        $signature = $this->repository->getByUuid($uuid->toString());
        if (!$signature) {
            throw new SignatureNotFoundException("Signature not found");
        }
        return $signature;
    }

    private function validateUserPermission(object $signature): void
    {
        if ($signature->user_id !== Auth::id()) {
            throw new UnauthorizedException("No permission to perform this operation.");
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
        return $this->isValidUrl($newSignaturePath)
            ? $existingSignature->signature_path
            : $this->replaceSignatureInS3($existingSignature, $newSignaturePath);
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

    private function updateCaches(int $userId, UuidInterface $uuid): void
    {
        $this->userCacheService->forgetUserListCache(self::CACHE_KEY_LIST, $userId);
        $this->userCacheService->forgetDataCacheByUuid(self::CACHE_KEY_SIGNATURE, $uuid->toString());
        $this->userCacheService->updateSuperAdminCaches(self::CACHE_KEY_LIST);
    }
}