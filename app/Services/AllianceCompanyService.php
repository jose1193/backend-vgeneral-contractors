<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\AllianceCompanyRepositoryInterface;
use App\DTOs\AllianceCompanyDTO;
use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Psr\Log\LoggerInterface;

class AllianceCompanyService
{
    private const CACHE_TIME = 720; // minutes
    private const CACHE_KEY_LIST = 'alliance_companies_total_list_';
    private const CACHE_KEY_COMPANY = 'alliance_company_';

    public function __construct(
        private readonly AllianceCompanyRepositoryInterface $repository,
        private readonly TransactionService $transactionService,
        private readonly UserCacheService $userCacheService,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Get all alliance companies
     */
    public function all(): Collection
    {
        try {
            return $this->userCacheService->getCachedUserList(
                self::CACHE_KEY_LIST,
                Auth::id(),
                fn() => $this->repository->index()
            );
        } catch (Exception $e) {
            $this->logger->error('Error fetching alliance companies', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Store a new alliance company
     */
    public function storeData(AllianceCompanyDTO $dto): object
    {
        return $this->transactionService->handleTransaction(function () use ($dto) {
            $details = $this->prepareCompanyDetails($dto);
            $company = $this->repository->store($details);
            
            $this->updateCaches(Auth::id(), Uuid::fromString($company->uuid));
            $this->logger->info('Alliance company stored successfully', ['uuid' => $company->uuid]);
            
            return $company;
        }, 'storing alliance company');
    }

    /**
     * Update an alliance company
     */
    public function updateData(AllianceCompanyDTO $dto, UuidInterface $uuid): object
    {
        return $this->transactionService->handleTransaction(function () use ($dto, $uuid) {
            $existingCompany = $this->getExistingCompany($uuid);
            
            if ($dto->allianceCompanyName !== $existingCompany->alliance_company_name) {
                $this->ensureCompanyNameIsUnique($dto->allianceCompanyName, $uuid);
            }
            
            $updateDetails = $this->prepareUpdateDetails($dto, $uuid);
            $company = $this->repository->update($updateDetails, $uuid->toString());

            $this->updateCaches(Auth::id(), $uuid);
            $this->logger->info('Alliance company updated successfully', ['uuid' => $uuid->toString()]);
            
            return $company;
        }, 'updating alliance company');
    }

    private function ensureCompanyNameIsUnique(string $name, UuidInterface $excludeUuid): void
    {
        try {
            $existingCompany = $this->repository->findByName($name);
            
            if ($existingCompany && $existingCompany->uuid !== $excludeUuid->toString()) {
                throw new Exception("An alliance company with this name already exists.");
            }
        } catch (Exception $e) {
            $this->logger->error('Error checking company name uniqueness', [
                'name' => $name,
                'exclude_uuid' => $excludeUuid->toString(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get a specific alliance company
     */
    public function showData(UuidInterface $uuid): ?object
    {
        try {
            return $this->userCacheService->getCachedItem(
                self::CACHE_KEY_COMPANY,
                $uuid->toString(),
                fn() => $this->repository->getByUuid($uuid->toString())
            );
        } catch (Exception $e) {
            $this->logger->error('Error retrieving alliance company', [
                'uuid' => $uuid->toString(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Delete an alliance company
     */
    public function deleteData(UuidInterface $uuid): bool
    {
        return $this->transactionService->handleTransaction(function () use ($uuid) {
            $existingCompany = $this->getExistingCompany($uuid);
            
            $this->repository->delete($uuid->toString());
            $this->updateCaches(Auth::id(), $uuid);
            
            $this->logger->info('Alliance company deleted successfully', [
                'uuid' => $uuid->toString()
            ]);
            
            return true;
        }, 'deleting alliance company');
    }

    /**
     * Get existing company or throw exception
     */
    private function getExistingCompany(UuidInterface $uuid): object
    {
        $company = $this->repository->getByUuid($uuid->toString());
        if (!$company) {
            throw new Exception("Alliance company not found");
        }
        return $company;
    }

    /**
     * Prepare company details for creation
     */
    private function prepareCompanyDetails(AllianceCompanyDTO $dto): array
    {
        return [
            ...$dto->toArray(),
            'uuid' => Uuid::uuid4()->toString(),
            'user_id' => Auth::id(),
        ];
    }

    /**
     * Prepare company details for update
     */
    private function prepareUpdateDetails(AllianceCompanyDTO $dto, UuidInterface $uuid): array
    {
        return [
            ...$dto->toArray(),
            'uuid' => $uuid->toString(),
            'user_id' => Auth::id(),
        ];
    }

    /**
     * Update all related caches
     */
    private function updateCaches(int $userId, UuidInterface $uuid): void
    {
        $this->userCacheService->forgetUserListCache(self::CACHE_KEY_LIST, $userId);
        $this->userCacheService->forgetDataCacheByUuid(self::CACHE_KEY_COMPANY, $uuid->toString());
        $this->userCacheService->updateSuperAdminCaches(self::CACHE_KEY_LIST);
    }
}