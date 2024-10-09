<?php

namespace App\DTOs;

use Ramsey\Uuid\Uuid;

class ClaimDTO
{
    public function __construct(
        public readonly ?Uuid $uuid,
        public readonly ?int $propertyId,
        public readonly ?int $signaturePathId,
        public readonly ?int $typeDamageId,
        public readonly ?int $userIdRefBy,
        public readonly ?string $policyNumber,
        public readonly ?string $claimInternalId,
        public readonly ?string $dateOfLoss,
        public readonly ?string $descriptionOfLoss,
        public readonly ?string $claimDate,
        public readonly ?int $claimStatus,
        public readonly ?string $damageDescription,
        public readonly ?string $scopeOfWork,
        public readonly ?bool $customerReviewed,
        public readonly ?string $claimNumber,
        public readonly ?int $numberOfFloors,
        public readonly ?int $allianceCompanyId,
        public readonly ?array $serviceRequestIds,
        public readonly ?array $causeOfLossIds,
        public readonly ?int $insuranceAdjusterId,
        public readonly ?int $publicAdjusterId,
        public readonly ?int $publicCompanyId,
        public readonly ?int $insuranceCompanyId,
        public readonly ?string $workDate,
        public readonly ?array $technicalUserIds,
        public readonly ?string $dayOfLossAgo,
        public readonly ?bool $neverHadPriorLoss,
        public readonly ?bool $hasNeverHadPriorLoss,
        public readonly ?float $amountPaid,
        public readonly ?string $description,
        public readonly ?array $affidavit
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: isset($data['uuid']) ? Uuid::fromString($data['uuid']) : null,
            propertyId: $data['property_id'] ?? null,
            signaturePathId: $data['signature_path_id'] ?? null,
            typeDamageId: $data['type_damage_id'] ?? null,
            userIdRefBy: $data['user_id_ref_by'] ?? null,
            policyNumber: $data['policy_number'] ?? null,
            claimInternalId: $data['claim_internal_id'] ?? null,
            dateOfLoss: $data['date_of_loss'] ?? null,
            descriptionOfLoss: $data['description_of_loss'] ?? null,
            claimDate: $data['claim_date'] ?? null,
            claimStatus: $data['claim_status'] ?? null,
            damageDescription: $data['damage_description'] ?? null,
            scopeOfWork: $data['scope_of_work'] ?? null,
            customerReviewed: $data['customer_reviewed'] ?? null,
            claimNumber: $data['claim_number'] ?? null,
            numberOfFloors: $data['number_of_floors'] ?? null,
            allianceCompanyId: $data['alliance_company_id'] ?? null,
            serviceRequestIds: $data['service_request_id'] ?? null,
            causeOfLossIds: $data['cause_of_loss_id'] ?? null,
            insuranceAdjusterId: $data['insurance_adjuster_id'] ?? null,
            publicAdjusterId: $data['public_adjuster_id'] ?? null,
            publicCompanyId: $data['public_company_id'] ?? null,
            insuranceCompanyId: $data['insurance_company_id'] ?? null,
            workDate: $data['work_date'] ?? null,
            technicalUserIds: $data['technical_user_id'] ?? null,
            dayOfLossAgo: $data['day_of_loss_ago'] ?? null,
            neverHadPriorLoss: $data['never_had_prior_loss'] ?? null,
            hasNeverHadPriorLoss: $data['has_never_had_prior_loss'] ?? null,
            amountPaid: $data['amount_paid'] ?? null,
            description: $data['description'] ?? null,
            affidavit: $data['affidavit'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid?->toString(),
            'property_id' => $this->propertyId,
            'signature_path_id' => $this->signaturePathId,
            'type_damage_id' => $this->typeDamageId,
            'user_id_ref_by' => $this->userIdRefBy,
            'policy_number' => $this->policyNumber,
            'claim_internal_id' => $this->claimInternalId,
            'date_of_loss' => $this->dateOfLoss,
            'description_of_loss' => $this->descriptionOfLoss,
            'claim_date' => $this->claimDate,
            'claim_status' => $this->claimStatus,
            'damage_description' => $this->damageDescription,
            'scope_of_work' => $this->scopeOfWork,
            'customer_reviewed' => $this->customerReviewed,
            'claim_number' => $this->claimNumber,
            'number_of_floors' => $this->numberOfFloors,
            'alliance_company_id' => $this->allianceCompanyId,
            'service_request_id' => $this->serviceRequestIds,
            'cause_of_loss_id' => $this->causeOfLossIds,
            'insurance_adjuster_id' => $this->insuranceAdjusterId,
            'public_adjuster_id' => $this->publicAdjusterId,
            'public_company_id' => $this->publicCompanyId,
            'insurance_company_id' => $this->insuranceCompanyId,
            'work_date' => $this->workDate,
            'technical_user_id' => $this->technicalUserIds,
            'day_of_loss_ago' => $this->dayOfLossAgo,
            'never_had_prior_loss' => $this->neverHadPriorLoss,
            'has_never_had_prior_loss' => $this->hasNeverHadPriorLoss,
            'amount_paid' => $this->amountPaid,
            'description' => $this->description,
            'affidavit' => $this->affidavit
        ];
    }
}