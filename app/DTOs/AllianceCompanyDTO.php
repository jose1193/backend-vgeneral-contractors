<?php

namespace App\DTOs;

use Ramsey\Uuid\Uuid;

class AllianceCompanyDTO 
{
    public function __construct(
        public readonly ?Uuid $uuid,
        public readonly string $allianceCompanyName,
        public readonly ?string $email,
        public readonly ?string $phone,
        public readonly ?string $address,
        public readonly ?string $website,
        public readonly ?string $userId
    ) {}

    public static function fromArray(array $data): self 
    {
        return new self(
            uuid: isset($data['uuid']) ? Uuid::fromString($data['uuid']) : null,
            allianceCompanyName: $data['alliance_company_name'],
            email: $data['email'] ?? null,
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
            website: $data['website'] ?? null,
            userId: $data['user_id'] ?? null
        );
    }

    public function toArray(): array 
    {
        return [
            'uuid' => $this->uuid?->toString(),
            'alliance_company_name' => $this->allianceCompanyName,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'website' => $this->website,
            'user_id' => $this->userId
        ];
    }
}