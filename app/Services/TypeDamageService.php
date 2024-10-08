<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\TypeDamageRepositoryInterface;
use App\DTOs\TypeDamageDTO;
use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

class TypeDamageService
{
    private const CACHE_KEY_TYPE_DAMAGE = 'type_damage_';
    private const CACHE_KEY_LIST = 'type_damages_total_list';

    public function __construct(
        private readonly TypeDamageRepositoryInterface $repository,
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

    public function storeData(TypeDamageDTO $typeDamageDto): object
    {
        return $this->transactionService->handleTransaction(function () use ($typeDamageDto) {
            $this->ensureTypeDamageNameIsUnique($typeDamageDto->typeDamageName);
            $typeDamageDetails = $this->prepareTypeDamageDetails($typeDamageDto);
            $typeDamage = $this->repository->store($typeDamageDetails);
            $this->updateCaches(Auth::id(), Uuid::fromString($typeDamage->uuid));
            $this->logger->info('Type damage stored successfully', ['uuid' => $typeDamage->uuid]);
            return $typeDamage;
        }, 'storing type damage');
    }

    public function updateData(TypeDamageDTO $typeDamageDto, UuidInterface $uuid): ?object
    {
        return $this->transactionService->handleTransaction(function () use ($typeDamageDto, $uuid) {
            $existingTypeDamage = $this->getExistingTypeDamage($uuid);
            $this->validateUserPermission();
            
            if ($typeDamageDto->typeDamageName !== null) {
                $this->ensureTypeDamageNameIsUnique($typeDamageDto->typeDamageName, $uuid);
            }
            
            $updateDetails = $this->prepareUpdateDetails($typeDamageDto);
            $typeDamage = $this->repository->update($updateDetails, $uuid->toString());
            
            $this->updateCaches(Auth::id(), $uuid);
            $this->logger->info('Type damage updated successfully', ['uuid' => $uuid->toString()]);
            return $typeDamage;
        }, 'updating type damage');
    }

    public function showData(UuidInterface $uuid): ?object
    {
        return $this->userCacheService->getCachedItem(
            self::CACHE_KEY_TYPE_DAMAGE,
            $uuid->toString(),
            fn() => $this->repository->getByUuid($uuid->toString())
        );
    }

    public function deleteData(UuidInterface $uuid): bool
    {
        return $this->transactionService->handleTransaction(function () use ($uuid) {
            $this->getExistingTypeDamage($uuid);
            $this->validateUserPermission();
            
            $this->repository->delete($uuid->toString());
            $this->updateCaches(Auth::id(), $uuid);
            
            $this->logger->info('Type damage deleted successfully', ['uuid' => $uuid->toString()]);
            return true;
        }, 'deleting type damage');
    }

    private function ensureTypeDamageNameIsUnique(string $name, ?UuidInterface $uuid = null): void
    {
        $existingTypeDamage = $this->repository->findByName($name);
        if ($existingTypeDamage && (!$uuid || $existingTypeDamage->uuid !== $uuid->toString())) {
            throw new Exception("The type damage name is already in use by another record.");
        }
    }
    
    private function prepareTypeDamageDetails(TypeDamageDTO $dto): array
    {
        return [
            ...$dto->toArray(),
            'uuid' => Uuid::uuid4()->toString(),
            'user_id' => Auth::id(),
        ];
    }

    private function prepareUpdateDetails(TypeDamageDTO $dto): array
    {
        $updateDetails = [];
        
        if ($dto->typeDamageName !== null) {
            $updateDetails['type_damage_name'] = $dto->typeDamageName;
        }
        if ($dto->description !== null) {
            $updateDetails['description'] = $dto->description;
        }
        if ($dto->severity !== null) {
            $updateDetails['severity'] = $dto->severity;
        }

        $updateDetails['user_id'] = Auth::id();

        return $updateDetails;
    }

    private function getExistingTypeDamage(UuidInterface $uuid): object
    {
        $typeDamage = $this->repository->getByUuid($uuid->toString());
        if (!$typeDamage) {
            throw new Exception("Type damage not found");
        }
        return $typeDamage;
    }

    private function validateUserPermission(): void
    {
        if (!$this->repository->isSuperAdmin(Auth::id())) {
            throw new Exception("No permission to perform this operation.");
        }
    }

    private function updateCaches(int $userId, UuidInterface $uuid): void
    {
        $this->userCacheService->forgetUserListCache(self::CACHE_KEY_LIST, $userId);
        $this->userCacheService->forgetDataCacheByUuid(self::CACHE_KEY_TYPE_DAMAGE, $uuid->toString());
        $this->userCacheService->updateSuperAdminCaches(self::CACHE_KEY_LIST);
    }
}