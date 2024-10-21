<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\ClaimRepositoryInterface;
use App\DTOs\ClaimDTO;
use App\DTOs\ClaimAgreementFullDTO;
use Exception;
use App\Models\User;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\PublicAdjusterAssignmentNotification;
use App\Mail\TechnicalUserAssignmentNotification;
use Psr\Log\LoggerInterface;

class ClaimService
{
    private const CACHE_KEY_CLAIM = 'claim_';
    private const CACHE_KEY_LIST = 'claims_total_list_';
    
    // New constants for field names
    private const CLAIM_FIELDS = [
        'uuid', 'property_id', 'signature_path_id', 'type_damage_id', 'policy_number',
        'claim_internal_id', 'date_of_loss', 'description_of_loss', 'claim_date', 'claim_status',
        'damage_description', 'scope_of_work', 'customer_reviewed', 'claim_number',
        'number_of_floors', 'alliance_company_id', 'insurance_adjuster_id', 'public_adjuster_id',
        'public_company_id', 'insurance_company_id', 'work_date', 'day_of_loss_ago',
        'never_had_prior_loss', 'has_never_had_prior_loss', 'amount_paid', 'description'
    ];

    private const ASSIGNMENTS = [
        'insurance_company' => 'InsuranceCompanyAssignment',
        'insurance_adjuster' => 'InsuranceAdjusterAssignment',
        'public_adjuster' => 'PublicAdjusterAssignment',
        'public_company' => 'PublicCompanyAssignment',
        'alliance_company' => 'claimAlliance',
    ];

    public function __construct(
        private readonly ClaimRepositoryInterface $repository,
        private readonly TransactionService $transactionService,
        private readonly UserCacheService $userCacheService,
        private readonly LoggerInterface $logger,
        private readonly ClaimAgreementFullService $claimAgreementFullService
    ) {}

    public function all(): Collection
    {
        return $this->userCacheService->getCachedUserList(
            self::CACHE_KEY_LIST,
            Auth::id(),
            fn() => $this->repository->getClaimsByUser(Auth::user())
        );
    }

    public function storeData(ClaimDTO $claimDto, array $technicalIds, array $serviceRequestIds, array $causeOfLoss): object
    {
        return $this->transactionService->handleTransaction(function () use ($claimDto, $technicalIds, $serviceRequestIds, $causeOfLoss) {
            $claimDetails = $this->prepareClaimDetails($claimDto);
            $claim = $this->repository->store($claimDetails);

            $this->handleRelatedData($claim, $technicalIds, $serviceRequestIds, $causeOfLoss);
            $this->handleAssignments($claim, $claimDetails);

            // Generate and store the agreement
            $this->generateClaimAgreement($claim);

            $this->updateCaches($claimDetails['user_id_ref_by']);
            $this->logger->info('Claim stored successfully', ['claim_id' => $claim->id]);
            return $claim;
        }, 'storing claim');
    }

    
    public function updateData(ClaimDTO $claimDto, UuidInterface $uuid, array $technicalIds, array $serviceRequestIds, array $causeOfLoss): ?object
    {
        return $this->transactionService->handleTransaction(function () use ($claimDto, $uuid, $technicalIds, $serviceRequestIds, $causeOfLoss) {
            $existingClaim = $this->getExistingClaim($uuid);
            
            $updateDetails = $this->prepareUpdateDetails($claimDto);
            $updateDetails['technical_user_id'] = $technicalIds; 
            $claim = $this->repository->update($updateDetails, $uuid->toString());
            
            $this->handleRelatedData($claim, $technicalIds, $serviceRequestIds, $causeOfLoss);
            $this->handleAssignments($claim, $updateDetails);
            
            $this->updateCaches($updateDetails['user_id_ref_by'], $uuid);
            $this->logger->info('Claim updated successfully', ['claim_id' => $claim->id]);
            return $claim;
        }, 'updating claim');
    }

    public function showData(UuidInterface $uuid): ?object
    {
        return $this->userCacheService->getCachedItem(
            self::CACHE_KEY_CLAIM,
            $uuid->toString(),
            fn() => $this->repository->getByUuid($uuid->toString())
        );
    }

    public function deleteData(UuidInterface $uuid): bool
    {
        return $this->transactionService->handleTransaction(function () use ($uuid) {
            $existingClaim = $this->getExistingClaim($uuid);
            
            $this->repository->delete($uuid->toString());
            $this->updateCaches($existingClaim->user_id_ref_by, $uuid);
            
            $this->logger->info('Claim deleted successfully', ['claim_uuid' => $uuid->toString()]);
            return true;
        }, 'deleting claim');
    }

    public function restoreData(UuidInterface $uuid): ?object
    {
        return $this->transactionService->handleTransaction(function () use ($uuid) {
            $claim = $this->repository->restore($uuid->toString());
            $this->updateCaches($claim->user_id_ref_by, $uuid);
            $this->logger->info('Claim restored successfully', ['claim_id' => $claim->id]);
            return $claim;
        }, 'restoring claim');
    }

    private function prepareClaimDetails(ClaimDTO $dto): array
    {
        $year = (int) date('Y');
        $sequenceNumber = $this->getNextSequenceNumber($year);
        
        return [
            ...$dto->toArray(),
            'uuid' => Uuid::uuid4()->toString(),
            'user_id_ref_by' => $dto->userIdRefBy ?? Auth::id(),
            'claim_date' => now(),
            'claim_status' => 1,
            'claim_internal_id' => "VG-CLAIM-{$year}-{$sequenceNumber}",
            'signature_path_id' => $this->repository->getSignaturePathId(),
        ];
    }

    private function prepareUpdateDetails(ClaimDTO $dto): array
    {
        $updateDetails = ['user_id_ref_by' => $dto->userIdRefBy ?? Auth::id()];

        foreach (self::CLAIM_FIELDS as $field) {
            $dtoField = lcfirst(str_replace('_', '', ucwords($field, '_')));
            if (property_exists($dto, $dtoField) && $dto->$dtoField !== null) {
                $updateDetails[$field] = $dto->$dtoField instanceof UuidInterface
                    ? $dto->$dtoField->toString()
                    : $dto->$dtoField;
            }
        }

        return $updateDetails;
    }

    private function getNextSequenceNumber(int $year): string
    {
        return $this->transactionService->handleTransaction(function () use ($year) {
            $latestClaim = DB::table('claims')
                ->whereYear('created_at', $year)
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();
        
            $sequenceNumber = $latestClaim ? (int)substr($latestClaim->claim_internal_id, -4) + 1 : 1;
            return str_pad((string)$sequenceNumber, 4, '0', STR_PAD_LEFT);
        }, 'getting next sequence number');
    }

    private function getExistingClaim(UuidInterface $uuid): object
    {
        $claim = $this->repository->getByUuid($uuid->toString());
        if (!$claim) {
            $this->logger->error('Claim not found', ['uuid' => $uuid->toString()]);
            throw new Exception("Claim not found with UUID: {$uuid->toString()}");
        }
        return $claim;
    }

    private function handleRelatedData(object $claim, array $technicalIds, array $serviceRequestIds, array $causeOfLoss): void
    {
        $this->transactionService->handleTransaction(function () use ($claim, $technicalIds, $serviceRequestIds, $causeOfLoss) {
            $claim->causesOfLoss()->sync($causeOfLoss);
            $claim->serviceRequests()->sync($serviceRequestIds);
            
            if (isset($claim->affidavit) && is_array($claim->affidavit)) {
                $affidavitData = $claim->affidavit + ['uuid' => Uuid::uuid4()->toString()];
                $this->repository->storeAffidavitForm($affidavitData, $claim->id);
            }
        }, 'handling related data');
    }

    private function handleAssignments(object $claim, array $details): void
    {
        foreach (self::ASSIGNMENTS as $key => $model) {
            $this->handleSingleAssignment($claim, $details, "{$key}_id", $model);
        }

        $this->handleTechnicalUserAssignment($claim, $details);
    }

    private function handleSingleAssignment(object $claim, array $details, string $idKey, string $model): void
    {
        $id = $details[$idKey] ?? null;
        $relation = lcfirst($model);

        if ($id === null || $id === '' || $id === 0) {
            $claim->$relation()->delete();
        } elseif ((int)$id > 0) {
            $assignment = $claim->$relation()->updateOrCreate(
                ['claim_id' => $claim->id],
                ["{$idKey}" => $id, 'assignment_date' => now()]
            );

            $this->sendAssignmentNotification($model, $id, $assignment, $claim);
        }
    }

    private function sendAssignmentNotification(string $model, int $id, $assignment, object $claim): void
    {
        if ($model === 'PublicAdjusterAssignment' && ($assignment->wasRecentlyCreated || $assignment->wasChanged("{$model}_id"))) {
            $publicAdjuster = $this->repository->findByUserId($id);
            if ($publicAdjuster) {
                Mail::to($publicAdjuster->email)->send(new PublicAdjusterAssignmentNotification($publicAdjuster, $claim));
                $this->logger->info('Public adjuster assignment notification sent', ['adjuster_id' => $id, 'claim_id' => $claim->id]);
            }
        }
    }

    private function handleTechnicalUserAssignment(object $claim, array $details): void
    {
        $technicalUserIds = $details['technical_user_id'] ?? [];
    
        if (empty($technicalUserIds)) {
            $claim->technicalAssignments()->delete();
            return;
        }
    
        $currentAssignmentIds = $claim->technicalAssignments()->pluck('technical_user_id')->toArray();
    
        $claim->technicalAssignments()->whereNotIn('technical_user_id', $technicalUserIds)->delete();
    
        foreach ($technicalUserIds as $technicalUserId) {
            $assignment = $claim->technicalAssignments()->updateOrCreate(
                ['technical_user_id' => $technicalUserId],
                [
                    'assignment_status' => $details['assignment_status'] ?? 'Pending',
                    'assignment_date' => now(),
                    'work_date' => $details['work_date'] ?? null,
                ]
            );
            
            $this->sendTechnicalUserNotification($technicalUserId, $assignment, $claim, $currentAssignmentIds);
        }
    }

    private function sendTechnicalUserNotification(int $technicalUserId, $assignment, object $claim, array $currentAssignmentIds): void
    {
        if ($assignment->wasRecentlyCreated || !in_array($technicalUserId, $currentAssignmentIds)) {
            $technicalUser = $this->repository->findByUserId($technicalUserId);
            if ($technicalUser) {
                Mail::to($technicalUser->email)->send(new TechnicalUserAssignmentNotification($technicalUser, $claim));
                $this->logger->info('Technical user assignment notification sent', ['user_id' => $technicalUserId, 'claim_id' => $claim->id]);
            }
        }
    }

    private function generateClaimAgreement(object $claim): void
    {
        try {
            $agreementDto = new ClaimAgreementFullDTO(
                uuid: null,
                claimUuid: $claim->uuid,
                fullPdfPath: null,
                agreementType: 'Agreement',
                generatedBy: Auth::id()
            );
            
            $agreement = $this->claimAgreementFullService->storeData($agreementDto);
            $this->logger->info('Claim agreement generated automatically', ['claim_id' => $claim->id, 'agreement_id' => $agreement->id]);
        } catch (Exception $e) {
            $this->logger->error('Failed to generate claim agreement automatically', ['claim_id' => $claim->id, 'error' => $e->getMessage()]);
            // Note: We're not re-throwing the exception here to avoid rolling back the claim creation
            // You might want to handle this differently based on your business requirements
        }
    }

    private function updateCaches(int $userId, ?UuidInterface $uuid = null): void
    {
        $this->userCacheService->forgetUserListCache(self::CACHE_KEY_LIST, $userId);
        $this->userCacheService->updateSuperAdminCaches(self::CACHE_KEY_LIST);
        
        if ($uuid) {
            $this->userCacheService->forgetDataCacheByUuid(self::CACHE_KEY_CLAIM, $uuid->toString());
        }
    }
}