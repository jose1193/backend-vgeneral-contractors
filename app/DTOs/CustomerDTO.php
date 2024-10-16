<?php

namespace App\DTOs;

use Ramsey\Uuid\Uuid;

class CustomerDTO
{
    public function __construct(
        public readonly ?Uuid $uuid,
        public readonly string $name,
        public readonly ?string $lastName,
        public readonly string $email,
        public readonly ?string $cellPhone,
        public readonly ?string $homePhone,
        public readonly ?string $occupation,
        public readonly ?int $userId,
        public readonly ?string $signatureData,
        public readonly ?int $propertyId 
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: isset($data['uuid']) ? Uuid::fromString($data['uuid']) : null,
            name: $data['name'],
            lastName: $data['last_name'] ?? null,
            email: $data['email'],
            cellPhone: $data['cell_phone'] ?? null,
            homePhone: $data['home_phone'] ?? null,
            occupation: $data['occupation'] ?? null,
            userId: $data['user_id'] ?? null,
            signatureData: $data['signature_data'] ?? null,
            propertyId: $data['property_id'] ?? null  
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid?->toString(),
            'name' => $this->name,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'cell_phone' => $this->cellPhone,
            'home_phone' => $this->homePhone,
            'occupation' => $this->occupation,
            'user_id' => $this->userId,
            'signature_data' => $this->signatureData,
            'property_id' => $this->propertyId 
        ];
    }
}
