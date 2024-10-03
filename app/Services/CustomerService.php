<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\CustomerRepositoryInterface;
use App\Interfaces\TransactionServiceInterface;
use App\Interfaces\CacheServiceInterface;
use App\DTOs\CustomerDTO;
use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CustomerService
{
    private const CACHE_TIME = 720; // minutes
    private const CACHE_KEY_LIST = 'customers_user_list_';

    public function __construct(
        private readonly CustomerRepositoryInterface $repository,
        private readonly TransactionService $transactionService,
        private readonly CacheService $cacheService
    ) {}

    public function allCustomers(): Collection
    {
        $cacheKey = $this->generateCacheKey(self::CACHE_KEY_LIST, (string) Auth::id());
        return $this->cacheService->getCachedData($cacheKey, self::CACHE_TIME, fn() => $this->repository->getByUser(Auth::user()));
    }

    public function storeCustomer(CustomerDTO $customerDto): object
    {
        return $this->transactionService->handleTransaction(function () use ($customerDto) {
            $customerDetails = $this->prepareCustomerDetails($customerDto);
            $customer = $this->repository->store($customerDetails);
            $this->updateDataCache();
            return $customer;
        }, 'storing customer');
    }

    public function updateCustomer(CustomerDTO $customerDto, UuidInterface $uuid): ?object
    {
        return $this->transactionService->handleTransaction(function () use ($customerDto, $uuid) {
            $existingCustomer = $this->getExistingCustomer($uuid);
            $this->validateUserPermission($existingCustomer);

            // Verificar si el email ya existe para otro cliente
            if ($customerDto->email && $this->emailExistsForOtherCustomer($customerDto->email, $uuid->toString())) {
                throw new Exception("The email has already been registered by another client.");
            }

            $updateDetails = $this->prepareUpdateDetails($customerDto);
            $customer = $this->repository->update($updateDetails, $uuid->toString());

            $this->updateCache($uuid, $customer);
            $this->updateDataCache();
            return $customer;
        }, 'updating customer');
    }

    private function emailExistsForOtherCustomer(string $email, string $excludeUuid): bool
    {
        return $this->repository->emailExistsForOtherCustomer($email, $excludeUuid);
    }

    public function showCustomer(UuidInterface $uuid): ?object
    {
        $cacheKey = $this->generateCacheKey('customer_', $uuid->toString());
        return $this->cacheService->getCachedData($cacheKey, self::CACHE_TIME, fn() => $this->repository->getByUuid($uuid->toString()));
    }

    public function deleteCustomer(UuidInterface $uuid): bool
    {
        return $this->transactionService->handleTransaction(function () use ($uuid) {
            $existingCustomer = $this->getExistingCustomer($uuid);
            $this->validateUserPermission($existingCustomer);
            
            $this->repository->delete($uuid->toString());
            $this->invalidateCustomerCache($uuid);
            $this->updateDataCache();
            return true;
        }, 'deleting customer');
    }

    public function restoreCustomer(UuidInterface $uuid): object
    {
        return $this->transactionService->handleTransaction(function () use ($uuid) {
            $customer = $this->repository->restore($uuid->toString());
            $this->updateCache($uuid, $customer);
            $this->updateDataCache();
            return $customer;
        }, 'restoring customer');
    }

    private function prepareCustomerDetails(CustomerDTO $dto): array
    {
        return [
            ...$dto->toArray(),
            'uuid' => Uuid::uuid4()->toString(),
            'user_id' => Auth::id(),
        ];
    }

    private function prepareUpdateDetails(CustomerDTO $dto): array
{
    $updateDetails = [
        'user_id_ref_by' => Auth::id(),
    ];

    $fields = [
        'name' => 'name',
        'email' => 'email',
        'cell_phone' => 'cellPhone',
        'home_phone' => 'homePhone',
        'occupation' => 'occupation',
        'last_name' => 'lastName',
        'signature_data' => 'signatureData'
    ];

    foreach ($fields as $dbField => $dtoField) {
        if (property_exists($dto, $dtoField) && $dto->$dtoField !== null) {
            $updateDetails[$dbField] = $dto->$dtoField;
        }
    }

    return $updateDetails;
}

    private function getExistingCustomer(UuidInterface $uuid): object
    {
        $customer = $this->repository->getByUuid($uuid->toString());
        if (!$customer) {
            throw new Exception("Customer not found");
        }
        return $customer;
    }

    private function validateUserPermission(): void
    {
        $userId = Auth::id();
        $isSuperAdmin = $this->repository->isSuperAdmin($userId);

        if (!$isSuperAdmin) {
            throw new Exception("Unauthorized access");
        }
    }

    private function updateCache(UuidInterface $uuid, object $customer): void
    {
        $cacheKey = $this->generateCacheKey('customer_', $uuid->toString());
        $this->cacheService->refreshCache($cacheKey, self::CACHE_TIME, fn() => $customer);
    }

    private function updateDataCache(): void
    {
        $cacheKey = $this->generateCacheKey(self::CACHE_KEY_LIST, (string) Auth::id());
        $this->cacheService->updateDataCache($cacheKey, self::CACHE_TIME, fn() => $this->repository->getByUser(Auth::user()));
    }

    private function invalidateCustomerCache(UuidInterface $uuid): void
    {
        $this->cacheService->invalidateCache($this->generateCacheKey('customer_', $uuid->toString()));
    }

    private function generateCacheKey(string $prefix, string $suffix): string
    {
        return $prefix . $suffix;
    }


    public function checkEmailAvailability(string $email, ?string $uuid = null): array
    {
        if ($uuid) {
            $customer = $this->repository->getByUuid($uuid);
            if (!$customer) {
                return [
                    'available' => false,
                    'message' => 'Customer not found with the provided UUID'
                ];
            }

            if ($customer->email === $email) {
                return [
                    'available' => true,
                    'message' => 'Current Client Email'
                ];
            }

            $exists = $this->repository->emailExistsForOtherCustomer($email, $uuid);
        } else {
            $exists = $this->repository->emailExists($email);
        }

        return [
            'available' => !$exists,
            'message' => $exists ? 'Email is already taken' : 'Email is available'
        ];
    }
}