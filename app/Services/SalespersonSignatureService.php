<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\SalespersonSignatureRepositoryInterface;
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
    private const CACHE_KEY_SIGNATURE = 'salesperson_signature_';
    private const SIGNATURE_S3_PATH = 'public/salesperson_signatures';

    public function __construct(
        private readonly SalespersonSignatureRepositoryInterface $repository,
        private readonly S3SignatureService $s3SignatureService,
        private readonly TransactionService $transactionService,
        private readonly UserCacheService $userCacheService,
        private readonly LoggerInterface $logger
    ) {}

    public function all(): Collection
    {   
        return $this->userCacheService->getCachedUserList(
            self::CACHE_KEY_LIST,
            Auth::id(),
            fn() => $this->repository->getSignaturesByUser(Auth::user())
        );
    }

    public function storeData(SalespersonSignatureDTO $dto): SalespersonSignature
    {
        return $this->transactionService->handleTransaction(function () use ($dto) {
            $user = $this->getAuthenticatedUser();
            $salespersonId = $this->getSalespersonId($user, $dto->salesPersonId);

            if ($this->repository->getBySalespersonId($salespersonId)) {
                throw new Exception("A signature already exists for this salesperson. Use updateData to modify it.");
            }

            $details = $this->prepareSignatureDetails($dto, $salespersonId);
            $signatureUrl = $this->s3SignatureService->storeSignature($details['signature_path'], self::SIGNATURE_S3_PATH);
            $details['signature_path'] = $signatureUrl;
            $signature = $this->repository->store($details);

            $this->updateCaches($salespersonId, $signature->uuid);
            $this->logger->info('Salesperson signature stored successfully', ['uuid' => $signature->uuid]);
            return $signature;
        }, 'storing salesperson signature');
    }

    public function updateData(SalespersonSignatureDTO $dto, UuidInterface $uuid): SalespersonSignature
    {
        return $this->transactionService->handleTransaction(function () use ($dto, $uuid) {
            $user = $this->getAuthenticatedUser();
            $existingSignature = $this->getExistingSignature($uuid);

            $this->validateUpdatePermission($user, $existingSignature, $dto);

            $updateDetails = $this->prepareUpdateDetails($dto, $uuid, $existingSignature);
            $signature = $this->repository->update($updateDetails, $uuid->toString());

            $this->updateCaches($signature->salesperson_id, $uuid);
            
            $this->logger->info('Salesperson signature updated successfully', ['uuid' => $uuid->toString()]);
            return $signature;
        }, 'updating salesperson signature');
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
            $this->validateSuperAdminPermission();

            $this->repository->delete($uuid->toString());
            $this->s3SignatureService->deleteSignature($existingSignature->signature_path);

            $this->updateCaches($existingSignature->salesperson_id, $uuid);

            $this->logger->info('Salesperson signature deleted successfully', ['uuid' => $uuid->toString()]);
            return true;
        }, 'deleting salesperson signature');
    }

    private function getAuthenticatedUser(): ?\App\Models\User
    {
        $user = Auth::user();
        if (!$user) {
            throw new Exception("No authenticated user found");
        }
        return $user;
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
        return $this->isValidUrl($newSignaturePath)
            ? $existingSignature->signature_path
            : $this->s3SignatureService->replaceSignature($existingSignature->signature_path, $newSignaturePath, self::SIGNATURE_S3_PATH);
    }
    
    private function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    private function updateCaches(int $salespersonId, UuidInterface $uuid): void
    {
        $this->userCacheService->forgetUserListCache(self::CACHE_KEY_LIST, $salespersonId);
        $this->userCacheService->forgetDataCacheByUuid(self::CACHE_KEY_SIGNATURE, $uuid->toString());
        $this->userCacheService->updateSuperAdminCaches(self::CACHE_KEY_LIST);
    }
}