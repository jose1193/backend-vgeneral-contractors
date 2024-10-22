<?php // app/Services/PublicCompanyService.php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\PublicCompanyRepositoryInterface;
use App\DTOs\PublicCompanyDTO;
use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Psr\Log\LoggerInterface;

class PublicCompanyService
{
    private const CACHE_TIME = 720; // minutes
    private const CACHE_KEY_LIST = 'public_companies_total_list_';
    private const CACHE_KEY_COMPANY = 'public_company_';

    public function __construct(
        private readonly PublicCompanyRepositoryInterface $repository,
        private readonly TransactionService $transactionService,
        private readonly UserCacheService $userCacheService,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * Get all public companies
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
            $this->logger->error('Error fetching public companies', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Store a new public company
     */
    public function storeData(PublicCompanyDTO $dto): object
    {
        return $this->transactionService->handleTransaction(function () use ($dto) {
            $details = $this->prepareCompanyDetails($dto);
            $company = $this->repository->store($details);
            
            $this->updateCaches(Auth::id(), Uuid::fromString($company->uuid));
            $this->logger->info('Public company stored successfully', ['uuid' => $company->uuid]);
            
            return $company;
        }, 'storing public company');
    }

    /**
     * Update a public company
     */
    public function updateData(PublicCompanyDTO $dto, UuidInterface $uuid): object
    {
        return $this->transactionService->handleTransaction(function () use ($dto, $uuid) {
            $existingCompany = $this->getExistingCompany($uuid);
            
        
            if ($dto->publicCompanyName !== $existingCompany->public_company_name) {
                $this->ensureCompanyNameIsUnique($dto->publicCompanyName, $uuid);
            }
            
            $updateDetails = $this->prepareUpdateDetails($dto, $uuid);
            $company = $this->repository->update($updateDetails, $uuid->toString());

            $this->updateCaches(Auth::id(), $uuid);
            $this->logger->info('Public company updated successfully', ['uuid' => $uuid->toString()]);
            
            return $company;
        }, 'updating public company');
    }

    private function ensureCompanyNameIsUnique(string $name, UuidInterface $excludeUuid): void
    {
        try {
            // Buscar si existe una compañía con el mismo nombre
            $existingCompany = $this->repository->findByName($name);
            
            // Si existe una compañía con ese nombre y es diferente a la que estamos actualizando
            if ($existingCompany && $existingCompany->uuid !== $excludeUuid->toString()) {
                throw new Exception("A public company with this name already exists.");
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
     * Get a specific public company
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
            $this->logger->error('Error retrieving public company', [
                'uuid' => $uuid->toString(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Delete a public company
     */
    public function deleteData(UuidInterface $uuid): bool
    {
        return $this->transactionService->handleTransaction(function () use ($uuid) {
            $existingCompany = $this->getExistingCompany($uuid);
            
            $this->repository->delete($uuid->toString());
            $this->updateCaches(Auth::id(), $uuid);
            
            $this->logger->info('Public company deleted successfully', [
                'uuid' => $uuid->toString()
            ]);
            
            return true;
        }, 'deleting public company');
    }

    /**
     * Get existing company or throw exception
     */
    private function getExistingCompany(UuidInterface $uuid): object
    {
        $company = $this->repository->getByUuid($uuid->toString());
        if (!$company) {
            throw new Exception("Public company not found");
        }
        return $company;
    }

    /**
     * Prepare company details for creation
     */
    private function prepareCompanyDetails(PublicCompanyDTO $dto): array
    {
        return [
            ...$dto->toArray(),
            'uuid' => Uuid::uuid4()->toString(),
        ];
    }

    /**
     * Prepare company details for update
     */
    private function prepareUpdateDetails(PublicCompanyDTO $dto, UuidInterface $uuid): array
    {
        return [
            ...$dto->toArray(),
            'uuid' => $uuid->toString(),
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