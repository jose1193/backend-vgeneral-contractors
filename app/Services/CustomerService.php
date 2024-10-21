<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\CustomerRepositoryInterface;
use App\Interfaces\PropertyRepositoryInterface;
use App\DTOs\CustomerDTO;
use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\User;

class CustomerService
{
    private const CACHE_TIME = 720; // minutes
    private const CACHE_KEY_CUSTOMER = 'customer_';
    private const CACHE_KEY_USER_LIST = 'customers_user_list_';

    public function __construct(
        private readonly CustomerRepositoryInterface $repository,
        private readonly PropertyRepositoryInterface $propertyRepository,
        private readonly TransactionService $transactionService,
        private readonly CacheService $cacheService,
        private readonly UserCacheService $userCacheService
    ) {}

    public function allCustomers(): Collection
    {
        return $this->userCacheService->getCachedUserList(
            self::CACHE_KEY_USER_LIST,
            Auth::id(),
            fn() => $this->repository->getByUser(Auth::user())
        );
    }

    public function storeCustomer(CustomerDTO $customerDto): object
    {
        return $this->transactionService->handleTransaction(function () use ($customerDto) {
            $customerDetails = $this->prepareCustomerDetails($customerDto);
            $customer = $this->repository->store($customerDetails);

            if ($customerDto->propertyId) {
                $this->associateCustomerWithProperty($customer->id, $customerDto->propertyId);
            }

            $this->updateCaches(Auth::id());
            return $customer;
        }, 'storing customer');
    }

    private function associateCustomerWithProperty(int $customerId, int $propertyId): void
    {
        $property = $this->propertyRepository->findById($propertyId);
        if (!$property) {
            throw new Exception("Property not found with ID: {$propertyId}");
        }

        $existingCustomers = $property->customers()->orderBy('customer_properties.created_at')->get();
        
        // Check if the property already has 3 customers
        if ($existingCustomers->count() >= 3) {
            throw new Exception("This property already has the maximum number of customers (3) associated with it.");
        }

        $role = $this->determineCustomerRole($existingCustomers->count());

        $customerIds = $existingCustomers->pluck('id')->push($customerId)->unique()->values()->toArray();

        $this->propertyRepository->update(
            [],  
            $property->uuid,
            $customerIds
        );

        // Update roles for all customers
        $this->updateCustomerRoles($property, $customerIds);
    }

    private function determineCustomerRole(int $existingCustomerCount): string
    {
        switch ($existingCustomerCount) {
            case 0:
                return 'owner';
            case 1:
                return 'co-owner';
            default:
                return 'additional-signer';
        }
    }

    private function updateCustomerRoles($property, array $customerIds): void
    {
        foreach ($customerIds as $index => $customerId) {
            $role = $this->determineCustomerRole($index);
            $property->customers()->updateExistingPivot($customerId, ['role' => $role]);
        }
    }

    public function updateCustomer(CustomerDTO $customerDto, UuidInterface $uuid): ?object
    {
        return $this->transactionService->handleTransaction(function () use ($customerDto, $uuid) {
            $existingCustomer = $this->getExistingCustomer($uuid);
            
            $this->validateUserPermission($existingCustomer);

            if ($customerDto->email && $this->emailExistsForOtherCustomer($customerDto->email, $uuid->toString())) {
                throw new Exception("The email has already been registered by another client.");
            }

            $updateDetails = $this->prepareUpdateDetails($customerDto);
            $customer = $this->repository->update($updateDetails, $uuid->toString());
            
            $this->updateCaches($existingCustomer->user_id, $uuid);
            return $customer;
        }, 'updating customer');
    }

    public function showCustomer(UuidInterface $uuid): ?object
    {
        return $this->userCacheService->getCachedItem(
            self::CACHE_KEY_CUSTOMER,
            $uuid->toString(),
            fn() => $this->repository->getByUuid($uuid->toString())
        );
    }

    public function deleteCustomer(UuidInterface $uuid): bool
    {
        return $this->transactionService->handleTransaction(function () use ($uuid) {
            $existingCustomer = $this->getExistingCustomer($uuid);
            $this->validateUserPermission($existingCustomer);
            
            $this->repository->delete($uuid->toString());
            $this->updateCaches($existingCustomer->user_id, $uuid);
            
            return true;
        }, 'deleting customer');
    }

    public function restoreCustomer(UuidInterface $uuid): object
    {
        return $this->transactionService->handleTransaction(function () use ($uuid) {
            $customer = $this->repository->restore($uuid->toString());
            $this->updateCaches($customer->user_id, $uuid);
            return $customer;
        }, 'restoring customer');
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

    private function validateUserPermission(object $customer): void
    {
        if (Auth::id() !== $customer->user_id && !$this->repository->isSuperAdmin(Auth::id())) {
            throw new Exception("Unauthorized: You can only update customers you have registered.");
        }
    }

    private function emailExistsForOtherCustomer(string $email, string $excludeUuid): bool
    {
        return $this->repository->emailExistsForOtherCustomer($email, $excludeUuid);
    }

    private function updateCaches(int $userId, ?UuidInterface $uuid = null): void
    {
        $this->userCacheService->forgetUserListCache(self::CACHE_KEY_USER_LIST, $userId);
        $this->userCacheService->updateSuperAdminCaches(self::CACHE_KEY_USER_LIST);
        
        if ($uuid) {
            $this->userCacheService->forgetDataCacheByUuid(self::CACHE_KEY_CUSTOMER, $uuid->toString());
        }
    }
}