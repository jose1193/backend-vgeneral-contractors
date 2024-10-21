<?php

namespace App\DTOs;

use Ramsey\Uuid\Uuid;

class ClaimAgreementFullDTO
{
    public function __construct(
        public readonly ?Uuid $uuid,
        public readonly string $claimUuid,
        public readonly ?string $fullPdfPath,
        public readonly ?string $agreementType,
        public readonly ?int $generatedBy
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: isset($data['uuid']) ? Uuid::fromString($data['uuid']) : null,
            claimUuid: $data['claim_uuid'],
            fullPdfPath: $data['full_pdf_path'] ?? null,
            agreementType: $data['agreement_type'] ?? null,
            generatedBy: $data['generated_by'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid?->toString(),
            'claim_uuid' => $this->claimUuid,
            'full_pdf_path' => $this->fullPdfPath,
            'agreement_type' => $this->agreementType,
            'generated_by' => $this->generatedBy
        ];
    }
}