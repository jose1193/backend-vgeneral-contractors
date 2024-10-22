<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\InsuranceCompanyRepositoryInterface;
use App\Interfaces\S3ServiceInterface;
use App\Interfaces\TransactionServiceInterface;
use App\DTOs\InsuranceCompanyDTO;
use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

class InsuranceCompanyService
{
    private const CACHE_TIME = 720; // minutes
    private const CACHE_KEY_LIST = 'insurance_companies_total_list_';
    private const CACHE_KEY_COMPANY = 'insurance_company_';

    public function __construct(
        private readonly InsuranceCompanyRepositoryInterface $repository,
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

    public function storeData(InsuranceCompanyDTO $dto, array $prohibitedAlliances): object
    {
        return $this->transactionService->handleTransaction(function () use ($dto, $prohibitedAlliances) {
            $this->ensureCompanyNameDoesNotExist($dto->insuranceCompanyName);
            $details = $this->prepareCompanyDetails($dto);
            $company = $this->createCompany($details, $prohibitedAlliances);
            
            $this->updateCaches(Auth::id(), Uuid::fromString($company->uuid));
            $this->logger->info('Insurance company stored successfully', ['uuid' => $company->uuid]);
            return $company;
        }, 'storing insurance company');
    }

    public function updateData(InsuranceCompanyDTO $dto, UuidInterface $uuid, array $prohibitedAlliances): object
    {
        return $this->transactionService->handleTransaction(function () use ($dto, $uuid, $prohibitedAlliances) {
            $existingCompany = $this->getExistingCompany($uuid);
            $this->validateUserPermission();
            $this->ensureCompanyNameIsUnique($dto->insuranceCompanyName, $uuid);

            $updateDetails = $this->prepareUpdateDetails($dto, $uuid);
            $company = $this->repository->update($updateDetails, $uuid->toString());
            $company->alliances()->sync($prohibitedAlliances);

            $this->updateCaches(Auth::id(), $uuid);
            
            $this->logger->info('Insurance company updated successfully', ['uuid' => $uuid->toString()]);
            return $company;
        }, 'updating insurance company');
    }

    public function showData(UuidInterface $uuid): ?object
    {
        return $this->userCacheService->getCachedItem(
            self::CACHE_KEY_COMPANY,
            $uuid->toString(),
            fn() => $this->repository->getByUuid($uuid->toString())
        );
    }

   public function deleteData(UuidInterface $uuid): bool
{
        return $this->transactionService->handleTransaction(function () use ($uuid) {
            // Obtener la compañía existente
            $existingCompany = $this->getExistingCompany($uuid);
        
            // Verificar los permisos del usuario
            $this->validateUserPermission();

        
            $associatedAlliances = $existingCompany->alliances()->get();

            // Desvincular todas las alianzas asociadas
            $existingCompany->alliances()->detach($associatedAlliances->pluck('id')->toArray());

            // Eliminar la compañía
            $this->repository->delete($uuid->toString());

            // Actualizar caches, logs, etc.
            $this->updateCaches(Auth::id(), $uuid);
        
            $this->logger->info('Insurance company deleted and all alliances detached', [
            'uuid' => $uuid->toString(),
            'detached_alliances' => $associatedAlliances->pluck('id')->toArray()
            ]);
        
            return true;
        }, 'deleting insurance company and detaching alliances');
}

        private function ensureCompanyNameDoesNotExist(string $name): void
        {
            if ($this->repository->findByName($name)) {
            throw new Exception("An insurance company with this name already exists.");
            }
        }

        private function prepareCompanyDetails(InsuranceCompanyDTO $dto): array
        {
            return [
            ...$dto->toArray(),
            'uuid' => Uuid::uuid4()->toString(),
            'user_id' => Auth::id(),
            ];
        }

        private function createCompany(array $details, array $prohibitedAlliances): object
        {
            $company = $this->repository->store($details);
            if (!empty($prohibitedAlliances)) {
            $company->alliances()->sync($prohibitedAlliances);
            }
            return $company;
        }

        private function getExistingCompany(UuidInterface $uuid): object
        {
            $company = $this->repository->getByUuid($uuid->toString());
            if (!$company) {
            throw new Exception("Insurance company not found");
            }
            return $company;
        }

        private function validateUserPermission(): void
        {

    
            // Verificar si el usuario es Super Admin
            if (!$this->repository->isSuperAdmin(Auth::id())) {
            throw new Exception("No permission to perform this operation.");
            }
        }

        private function ensureCompanyNameIsUnique(string $name, UuidInterface $uuid): void
    {
        $existingCompany = $this->repository->findByName($name);
        if ($existingCompany && $existingCompany->uuid !== $uuid->toString()) {
            throw new Exception("The insurance company name is already in use by another company.");
        }
    }

    private function prepareUpdateDetails(InsuranceCompanyDTO $dto, UuidInterface $uuid): array
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