<?php

namespace App\DTOs;

use Ramsey\Uuid\Uuid;

class SalespersonSignatureDTO
{
    public function __construct(
        public readonly ?Uuid $uuid,
        public readonly ?int $salesPersonId,
        public readonly ?string $signaturePath,
        public readonly ?int $userIdRefBy
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: isset($data['uuid']) ? Uuid::fromString($data['uuid']) : null,
            salesPersonId: $data['salesperson_id'] ?? null,
            signaturePath: $data['signature_path'] ?? null,
            userIdRefBy: $data['user_id_ref_by'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid?->toString(),
            'salesperson_id' => $this->salesPersonId,
            'signature_path' => $this->signaturePath,
            'user_id_ref_by' => $this->userIdRefBy,
        ];
    }
}