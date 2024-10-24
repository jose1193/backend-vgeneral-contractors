<?php

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

    public function storeData(PublicCompanyDTO $dto): object
    {
        return $this->transactionService->handleTransaction(function () use ($dto) {
            $this->ensureCompanyNameDoesNotExist($dto->publicCompanyName);
            $details = $this->prepareCompanyDetails($dto);
            $company = $this->repository->store($details);
            
            $this->updateCaches(Auth::id(), Uuid::fromString($company->uuid));
            $this->logger->info('Public company stored successfully', ['uuid' => $company->uuid]);
            
            return $company;
        }, 'storing public company');
    }

    public function updateData(PublicCompanyDTO $dto, UuidInterface $uuid): object
    {
        return $this->transactionService->handleTransaction(function () use ($dto, $uuid) {
            $existingCompany = $this->getExistingCompany($uuid);
            $this->validateUserPermission($existingCompany);
            
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

    public function deleteData(UuidInterface $uuid): bool     
    {         
        return $this->transactionService->handleTransaction(function () use ($uuid) {             
        $existingCompany = $this->getExistingCompany($uuid);
        
        // Verificar que sea super admin
        if (!$this->repository->isSuperAdmin(Auth::id())) {
            throw new Exception("Unauthorized: Only super administrators can delete public companies.");
        }
                          
        $this->repository->delete($uuid->toString());             
        $this->updateCaches(Auth::id(), $uuid);                          
        
        $this->logger->info('Public company deleted successfully', [                 
            'uuid' => $uuid->toString()             
        ]);                          
        
            return true;         
        }, 'deleting public company');     
    }

    
    private function validateUserPermission(object $company): void
    {
        if (Auth::id() !== $company->user_id && !$this->repository->isSuperAdmin(Auth::id())) {
            throw new Exception("Unauthorized: You can only update companies you have registered.");
        }
    }

    private function ensureCompanyNameDoesNotExist(string $name): void
    {
        if ($this->repository->findByName($name)) {
            throw new Exception("A public company with this name already exists.");
        }
    }

    private function ensureCompanyNameIsUnique(string $name, UuidInterface $excludeUuid): void
    {
        try {
            $existingCompany = $this->repository->findByName($name);
            
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

    private function getExistingCompany(UuidInterface $uuid): object
    {
        $company = $this->repository->getByUuid($uuid->toString());
        if (!$company) {
            throw new Exception("Public company not found");
        }
        return $company;
    }

    private function prepareCompanyDetails(PublicCompanyDTO $dto): array
    {
        return [
            ...$dto->toArray(),
            'uuid' => Uuid::uuid4()->toString(),
            'user_id' => Auth::id(),
        ];
    }

    private function prepareUpdateDetails(PublicCompanyDTO $dto, UuidInterface $uuid): array
    {
        return [
            ...$dto->toArray(),
            'uuid' => $uuid->toString(),
            'user_id' => Auth::id(),
        ];
    }

    private function updateCaches(int $userId, UuidInterface $uuid): void
    {
        $this->userCacheService->forgetUserListCache(self::CACHE_KEY_LIST, $userId);
        $this->userCacheService->forgetDataCacheByUuid(self::CACHE_KEY_COMPANY, $uuid->toString());
        $this->userCacheService->updateSuperAdminCaches(self::CACHE_KEY_LIST);
    }
}