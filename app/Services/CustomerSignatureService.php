<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\CustomerSignatureRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use App\DTOs\CustomerSignatureDTO;
use Exception;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;
use App\Models\Customer;

class CustomerSignatureService
{
    private const CACHE_TIME = 720; // minutes
    private const CACHE_KEY_LIST = 'customer_signatures_user_list_';
    private const CACHE_KEY_SIGNATURE = 'customer_signature_';
    private const SIGNATURE_S3_PATH = 'public/customer_signatures';

    public function __construct(
        private readonly CustomerSignatureRepositoryInterface $repository,
        private readonly S3SignatureService $s3SignatureService,
        private readonly TransactionService $transactionService,
        private readonly UserCacheService $userCacheService,
        private readonly LoggerInterface $logger
    ) {}

    public function all(): Collection
    {   
        $user = $this->getAuthenticatedUser();
        return $this->userCacheService->getCachedUserList(
            self::CACHE_KEY_LIST,
            $user->id,
            function() use ($user) {
                if ($this->repository->isSuperAdmin($user)) {
                    return $this->repository->getAllSignatures();
                } else {
                    return $this->repository->getSignaturesByUserId($user->id);
                }
            }
        );
    }

    public function storeData(CustomerSignatureDTO $dto): object
    {
        return $this->transactionService->handleTransaction(function () use ($dto) {
            $user = $this->getAuthenticatedUser();

            $details = $this->prepareSignatureDetails($dto, $user);
            $signatureUrl = $this->s3SignatureService->storeSignature($details['signature_data'], self::SIGNATURE_S3_PATH);
            $details['signature_data'] = $signatureUrl;
            $signature = $this->repository->store($details);

            $this->updateCaches($user->id, Uuid::fromString($signature->uuid));
            $this->logger->info('Customer signature stored successfully', ['uuid' => $signature->uuid]);
            return $signature;
        }, 'storing customer signature');
    }

    public function updateData(CustomerSignatureDTO $dto, UuidInterface $uuid): object
    {
        return $this->transactionService->handleTransaction(function () use ($dto, $uuid) {
            $user = $this->getAuthenticatedUser();
            $existingSignature = $this->getExistingSignature($uuid);

            $this->validateUpdatePermission($user, $existingSignature);
            $this->validateCustomerIdAndRole($user, $existingSignature, $dto->customerId);

            $updateDetails = $this->prepareUpdateDetails($dto, $uuid, $existingSignature);
            $signature = $this->repository->update($updateDetails, $uuid->toString());

            $this->updateCaches($user->id, $uuid);
            
            $this->logger->info('Customer signature updated successfully', ['uuid' => $uuid->toString()]);
            return $signature;
        }, 'updating customer signature');
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
            $user = $this->getAuthenticatedUser();
            $existingSignature = $this->getExistingSignature($uuid);
            $this->validateDeletePermission($user, $existingSignature);

            $this->repository->delete($uuid->toString());
            $this->s3SignatureService->deleteSignature($existingSignature->signature_data);

            $this->updateCaches($user->id, $uuid);

            $this->logger->info('Customer signature deleted successfully', ['uuid' => $uuid->toString()]);
            return true;
        }, 'deleting customer signature');
    }

    private function getAuthenticatedUser(): object
    {
        $user = Auth::user();
        if (!$user) {
            throw new Exception("No authenticated user found");
        }
        return $user;
    }

    private function prepareSignatureDetails(CustomerSignatureDTO $dto, object $user): array
    {
        $uuid = Uuid::uuid4();
        return [
            ...$dto->toArray(),
            'uuid' => $uuid->toString(),
            'user_id_ref_by' => $user->id,
        ];
    }

    private function getExistingSignature(UuidInterface $uuid): object
    {
        $signature = $this->repository->getByUuid($uuid->toString());
        if (!$signature) {
            throw new Exception("Signature not found");
        }
        return $signature;
    }

    private function validateUpdatePermission(object $user, object $signature): void
    {
        if ($signature->user_id_ref_by !== $user->id && !$this->repository->isSuperAdmin($user)) {
            throw new Exception("You don't have permission to update this signature.");
        }
    }

    private function validateDeletePermission(object $user, object $signature): void
    {
        if ($signature->user_id_ref_by !== $user->id && !$this->repository->isSuperAdmin($user)) {
            throw new Exception("You don't have permission to delete this signature.");
        }
    }

    private function validateCustomerIdAndRole(object $user, object $existingSignature, $newCustomerId): void
    {
        if ($newCustomerId !== null) {
            $newCustomerId = is_numeric($newCustomerId) ? (int)$newCustomerId : null;

            if ($newCustomerId !== null && $newCustomerId !== $existingSignature->customer_id) {
                if (!$this->repository->isSuperAdmin($user)) {
                    throw new Exception("You don't have permission to change the customer for this signature.");
                }

                $customer = Customer::find($newCustomerId);
                if (!$customer) {
                    throw new Exception("Invalid customer ID provided.");
                }
            }
        }
    }

    private function prepareUpdateDetails(CustomerSignatureDTO $dto, UuidInterface $uuid, object $existingSignature): array
    {
        $updateDetails = [
            'uuid' => $uuid->toString(),
            'user_id_ref_by' => $existingSignature->user_id_ref_by,
        ];

        if ($dto->customerId !== null) {
            $updateDetails['customer_id'] = is_numeric($dto->customerId) ? (int)$dto->customerId : null;
        }

        if ($dto->signatureData !== null) {
            $updateDetails['signature_data'] = $this->handleSignatureDataUpdate($dto->signatureData, $existingSignature);
        }

        return $updateDetails;
    }

    private function handleSignatureDataUpdate(string $newSignatureData, object $existingSignature): string
    {
        return $this->isValidUrl($newSignatureData)
            ? $existingSignature->signature_data
            : $this->s3SignatureService->replaceSignature($existingSignature->signature_data, $newSignatureData, self::SIGNATURE_S3_PATH);
    }
    
    private function isValidUrl(string $url): bool
    {
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    private function updateCaches(int $userId, UuidInterface $uuid): void
    {
        $this->userCacheService->forgetUserListCache(self::CACHE_KEY_LIST, $userId);
        $this->userCacheService->forgetDataCacheByUuid(self::CACHE_KEY_SIGNATURE, $uuid->toString());
    }
}