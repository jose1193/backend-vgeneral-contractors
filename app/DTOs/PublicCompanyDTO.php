<?php

namespace App\DTOs;

use Ramsey\Uuid\Uuid;

class PublicCompanyDTO
{
    public function __construct(
        public readonly ?Uuid $uuid,
        public readonly string $publicCompanyName,
        public readonly ?string $address,
        public readonly ?string $phone,
        public readonly ?string $email,
        public readonly ?string $website,
        public readonly ?string $unit,
        public readonly ?int $userId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: isset($data['uuid']) ? Uuid::fromString($data['uuid']) : null,
            publicCompanyName: $data['public_company_name'],
            address: $data['address'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            website: $data['website'] ?? null,
            unit: $data['unit'] ?? null,
            userId: $data['user_id'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid?->toString(),
            'public_company_name' => $this->publicCompanyName,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
            'unit' => $this->unit,
            'user_id' => $this->userId,
        ];
    }
}