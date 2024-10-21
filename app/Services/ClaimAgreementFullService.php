<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\ClaimAgreementFullRepositoryInterface;
use App\DTOs\ClaimAgreementFullDTO;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;
use Exception;
use Psr\Log\LoggerInterface;
use Illuminate\Support\Collection;
use Ramsey\Uuid\UuidInterface;
class ClaimAgreementFullService
{
    private const CACHE_KEY_AGREEMENT = 'claim_agreement_';
    private const CACHE_KEY_LIST = 'claim_agreement_total_list_';
    private const CACHE_TIME = 720; // minutes

    public function __construct(
        private readonly ClaimAgreementFullRepositoryInterface $repository,
        private readonly TransactionService $transactionService,
        private readonly LoggerInterface $logger,
        private readonly DocumentGenerationService $documentGenerationService,
        private readonly FileManagementService $fileManagementService,
        private readonly UserCacheService $userCacheService
    ) {}

    public function all(): Collection
    {
        return $this->userCacheService->getCachedUserList(
            self::CACHE_KEY_LIST,
            Auth::id(),
            fn() => $this->repository->getClaimAgreementByUser(Auth::user())
        );
    }

     public function storeData(ClaimAgreementFullDTO $dto): object
    {
        return $this->transactionService->handleTransaction(function () use ($dto) {
            $this->validateStoreData($dto);
            $existingClaim = $this->repository->getClaimByUuid($dto->claimUuid);
            return $this->generateAndStoreAgreement($existingClaim, $dto->agreementType);
        }, 'storing claim agreement');
    }

    public function showData(UuidInterface $uuid): ?object
    {
        return $this->userCacheService->getCachedItem(
            self::CACHE_KEY_AGREEMENT,
            $uuid->toString(),
            fn() => $this->repository->getByUuid($uuid->toString())
        );
    }

      public function updateData(ClaimAgreementFullDTO $dto, UuidInterface $uuid): ?object
    {
        return $this->transactionService->handleTransaction(function () use ($dto, $uuid) {
            $existingFull = $this->getExistingAgreement($uuid->toString());
            
           
            if ($dto->agreementType !== null && $dto->agreementType !== $existingFull->agreement_type) {
               
                if ($dto->agreementType === 'Agreement') {
                    throw new Exception("Cannot change agreement type from 'Agreement Full' to 'Agreement'");
                }
               
                if ($dto->agreementType !== 'Agreement Full') {
                    throw new Exception("Invalid agreement type. Only 'Agreement Full' is allowed for updates.");
                }
            }

           
            $agreementType = $dto->agreementType ?? $existingFull->agreement_type;

            $this->fileManagementService->deleteFileFromStorage($existingFull->full_pdf_path);
            $this->repository->delete($uuid->toString());
            $existingClaim = $this->repository->getClaimByUuid($dto->claimUuid);
            
            $updatedAgreement = $this->generateAndStoreAgreement($existingClaim, $agreementType);
            
            $this->updateCaches($uuid->toString());
            $this->logger->info('Claim agreement updated', ['uuid' => $uuid->toString()]);
            
            return $updatedAgreement;
        }, 'updating claim agreement');
    }


    public function deleteData(UuidInterface $uuid): bool
    {
    return $this->transactionService->handleTransaction(function () use ($uuid) {
        $uuidString = $uuid->toString();
        $existingFull = $this->getExistingAgreement($uuidString);
        $this->fileManagementService->deleteFileFromStorage($existingFull->full_pdf_path);
        
        $result = $this->repository->delete($uuidString);
        
        if ($result) {
            $this->updateCaches($uuidString);
            $this->logger->info('Claim agreement deleted', ['uuid' => $uuidString]);
            return true;
        } else {
            $this->logger->error('Failed to delete claim agreement', ['uuid' => $uuidString]);
            return false;
        }
        }, 'deleting claim agreement');
    }

    private function validateStoreData(ClaimAgreementFullDTO $dto): void
    {
        if ($dto->claimUuid === null) {
            throw new Exception("Claim UUID is required");
        }
        if ($dto->agreementType === null) {
            throw new Exception("Agreement type is required");
        }
        if (!$this->repository->isClaimValid($dto->claimUuid)) {
            throw new Exception("The selected claim is invalid.");
        }
        if ($this->repository->checkClaimAgreementExists($dto->claimUuid, $dto->agreementType)) {
            throw new Exception("A claim agreement full with this agreement type already exists.");
        }
    }

    private function getExistingAgreement(string $uuid): object
    {
        $existingFull = $this->repository->getByUuid($uuid);
        if (!$existingFull) {
            throw new Exception("Claim agreement not found");
        }
        return $existingFull;
    }

    private function generateAndStoreAgreement(object $claim, string $agreementType): object
    {
        try {
            $filePaths = $this->documentGenerationService->generateDocumentAndStore($claim, $agreementType);
            $full = $this->storeFullInDatabase($claim, $filePaths['s3'], $agreementType);
            $this->updateCaches();
            $this->logger->info('Claim agreement stored successfully', ['agreement_id' => $full->id]);
            return $full;
        } catch (Exception $e) {
            $this->logger->error('Error generating/storing claim agreement: ' . $e->getMessage());
            throw $e;
        } finally {
            if (!empty($filePaths)) {
                $this->fileManagementService->cleanUpTempFiles($filePaths['local'] ?? null, $filePaths['processed'] ?? null);
            }
        }
    }

    private function storeFullInDatabase(object $claim, string $s3Path, string $agreementType): object
    {
        return $this->repository->store([
            'uuid' => Uuid::uuid4()->toString(),
            'claim_id' => $claim->id,
            'full_pdf_path' => $s3Path,
            'generated_by' => Auth::id(),
            'agreement_type' => $agreementType,
        ]);
    }

    private function updateCaches(?string $uuid = null): void
    {
        $userId = Auth::id();
        $this->userCacheService->forgetUserListCache(self::CACHE_KEY_LIST, $userId);
        $this->userCacheService->updateSuperAdminCaches(self::CACHE_KEY_LIST);
        
        if ($uuid !== null) {
            $this->userCacheService->forgetDataCacheByUuid(self::CACHE_KEY_AGREEMENT, $uuid);
            $this->logger->info('Cache cleared for claim agreement', ['uuid' => $uuid]);
        }
    }
}