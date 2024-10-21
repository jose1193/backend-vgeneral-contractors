<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\CauseOfLossRepositoryInterface;
use App\DTOs\CauseOfLossDTO;
use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;

class CauseOfLossService
{
    private const CACHE_KEY_CAUSE_OF_LOSS = 'cause_of_loss_';
    private const CACHE_KEY_LIST = 'cause_of_loss_list_';

    public function __construct(
        private readonly CauseOfLossRepositoryInterface $repository,
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

    public function storeData(CauseOfLossDTO $causeOfLossDto): object
    {
        return $this->transactionService->handleTransaction(function () use ($causeOfLossDto) {
            $causeOfLossDetails = $this->prepareCauseOfLossDetails($causeOfLossDto);
            $causeOfLoss = $this->repository->store($causeOfLossDetails);
            $this->updateCaches();
            return $causeOfLoss;
        }, 'storing cause of loss');
    }

    public function updateData(CauseOfLossDTO $causeOfLossDto, UuidInterface $uuid): ?object
    {
        return $this->transactionService->handleTransaction(function () use ($causeOfLossDto, $uuid) {
            $existingCauseOfLoss = $this->getExistingCauseOfLoss($uuid);
            
            if ($causeOfLossDto->causeOfLossName !== null) {
                $this->ensureCauseOfLossNameIsUnique($causeOfLossDto->causeOfLossName, $uuid);
            }
            
            $updateDetails = $this->prepareUpdateDetails($causeOfLossDto);
            $causeOfLoss = $this->repository->update($updateDetails, $uuid->toString());
            
            $this->updateCaches($uuid);
            return $causeOfLoss;
        }, 'updating cause of loss');
    }

    public function showData(UuidInterface $uuid): ?object
    {
        return $this->userCacheService->getCachedItem(
            self::CACHE_KEY_CAUSE_OF_LOSS,
            $uuid->toString(),
            fn() => $this->repository->getByUuid($uuid->toString())
        );
    }

    public function deleteData(UuidInterface $uuid): bool
    {
        return $this->transactionService->handleTransaction(function () use ($uuid) {
            $this->getExistingCauseOfLoss($uuid);
            
            $this->repository->delete($uuid->toString());
            $this->updateCaches($uuid);
            
            return true;
        }, 'deleting cause of loss');
    }

    private function ensureCauseOfLossNameIsUnique(string $name, ?UuidInterface $uuid = null): void
    {
        $existingCauseOfLoss = $this->repository->findByName($name);
        if ($existingCauseOfLoss && (!$uuid || $existingCauseOfLoss->uuid !== $uuid->toString())) {
            throw new Exception("The cause of loss name is already in use by another record.");
        }
    }
    
    private function prepareCauseOfLossDetails(CauseOfLossDTO $dto): array
    {
        return [
            ...$dto->toArray(),
            'uuid' => Uuid::uuid4()->toString(),
        ];
    }

    private function prepareUpdateDetails(CauseOfLossDTO $dto): array
    {
        $updateDetails = [];
        
        if ($dto->causeOfLossName !== null) {
            $updateDetails['cause_loss_name'] = $dto->causeOfLossName;
        }
        if ($dto->description !== null) {
            $updateDetails['description'] = $dto->description;
        }
        if ($dto->severity !== null) {
            $updateDetails['severity'] = $dto->severity;
        }

        return $updateDetails;
    }


    private function getExistingCauseOfLoss(UuidInterface $uuid): object
    {
        $causeOfLoss = $this->repository->getByUuid($uuid->toString());
        if (!$causeOfLoss) {
            throw new Exception("Cause of Loss not found");
        }
        return $causeOfLoss;
    }

    private function updateCaches(?UuidInterface $uuid = null): void
    {
        $this->userCacheService->forgetUserListCache(self::CACHE_KEY_LIST, Auth::id());
        $this->userCacheService->updateSuperAdminCaches(self::CACHE_KEY_LIST);
        
        if ($uuid !== null) {
        $this->userCacheService->forgetDataCacheByUuid(self::CACHE_KEY_CAUSE_OF_LOSS, $uuid->toString());
        }

    }
}