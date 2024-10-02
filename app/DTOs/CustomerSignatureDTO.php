<?php

namespace App\DTOs;

use Ramsey\Uuid\Uuid;

class CustomerSignatureDTO
{
    public function __construct(
        public readonly ?Uuid $uuid,
        public readonly string $customerId,
        public readonly ?string $signatureData,
        public readonly ?string $userIdRefBy
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: isset($data['uuid']) ? Uuid::fromString($data['uuid']) : null,
            customerId: $data['customer_id'],
            signatureData: $data['signature_data'] ?? null,
            userIdRefBy: $data['user_id_ref_by'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid?->toString(),
            'customer_id' => $this->customerId,
            'signature_data' => $this->signatureData,
            'user_id_ref_by' => $this->userIdRefBy,
        ];
    }
}