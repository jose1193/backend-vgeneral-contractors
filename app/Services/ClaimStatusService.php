<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\ClaimStatusRepositoryInterface;
use App\DTOs\ClaimStatusDTO;
use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class ClaimStatusService
{
    private const CACHE_KEY_CLAIM_STATUS = 'claim_status_';
    private const CACHE_KEY_LIST = 'claim_status_list_';

    public function __construct(
        private readonly ClaimStatusRepositoryInterface $repository,
        private readonly TransactionService $transactionService,
        private readonly UserCacheService $userCacheService
    ) {}

    public function all(): Collection
    {
        return $this->userCacheService->getCachedUserList(
            self::CACHE_KEY_LIST,
            Auth::id(),
            fn() => $this->repository->index()
        );
    }

    public function storeData(ClaimStatusDTO $claimStatusDto): object
    {
        return $this->transactionService->handleTransaction(function () use ($claimStatusDto) {
            $claimStatusDetails = $this->prepareClaimStatusDetails($claimStatusDto);
            $claimStatus = $this->repository->store($claimStatusDetails);
            $this->updateCaches();
            return $claimStatus;
        }, 'storing claim status');
    }

    public function updateData(ClaimStatusDTO $claimStatusDto, UuidInterface $uuid): ?object
    {
        return $this->transactionService->handleTransaction(function () use ($claimStatusDto, $uuid) {
            $existingClaimStatus = $this->getExistingClaimStatus($uuid);
            
            if ($claimStatusDto->claimStatusName !== null) {
                $this->ensureClaimStatusNameIsUnique($claimStatusDto->claimStatusName, $uuid);
            }
            
            $updateDetails = $this->prepareUpdateDetails($claimStatusDto);
            $claimStatus = $this->repository->update($updateDetails, $uuid->toString());
            
            $this->updateCaches($uuid);
            return $claimStatus;
        }, 'updating claim status');
    }

    public function showData(UuidInterface $uuid): ?object
    {
        return $this->userCacheService->getCachedItem(
            self::CACHE_KEY_CLAIM_STATUS,
            $uuid->toString(),
            fn() => $this->repository->getByUuid($uuid->toString())
        );
    }

    public function deleteData(UuidInterface $uuid): bool
    {
        return $this->transactionService->handleTransaction(function () use ($uuid) {
            $this->getExistingClaimStatus($uuid);
            
            $this->repository->delete($uuid->toString());
            $this->updateCaches($uuid);
            
            return true;
        }, 'deleting claim status');
    }

    private function ensureClaimStatusNameIsUnique(string $name, ?UuidInterface $uuid = null): void
    {
        $existingClaimStatus = $this->repository->findByName($name);
        if ($existingClaimStatus && (!$uuid || $existingClaimStatus->uuid !== $uuid->toString())) {
            throw new Exception("The claim status name is already in use by another record.");
        }
    }
    
    private function prepareClaimStatusDetails(ClaimStatusDTO $dto): array
    {
        return [
            ...$dto->toArray(),
            'uuid' => Uuid::uuid4()->toString(),
        ];
    }

    private function prepareUpdateDetails(ClaimStatusDTO $dto): array
    {
        $updateDetails = [];
        
        if ($dto->claimStatusName !== null) {
            $updateDetails['claim_status_name'] = $dto->claimStatusName;
        }
        if ($dto->backgroundColor !== null) {
            $updateDetails['background_color'] = $dto->backgroundColor;
        }

        return $updateDetails;
    }

    private function getExistingClaimStatus(UuidInterface $uuid): object
    {
        $claimStatus = $this->repository->getByUuid($uuid->toString());
        if (!$claimStatus) {
            throw new Exception("Claim Status not found");
        }
        return $claimStatus;
    }

    private function updateCaches(?UuidInterface $uuid = null): void
    {
        $this->userCacheService->forgetUserListCache(self::CACHE_KEY_LIST, Auth::id());
        $this->userCacheService->updateSuperAdminCaches(self::CACHE_KEY_LIST);
        
        if ($uuid) {
            $this->userCacheService->forgetDataCacheByUuid(self::CACHE_KEY_CLAIM_STATUS, $uuid->toString());
        }
    }
}
