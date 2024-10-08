<?php

namespace App\DTOs;

use Ramsey\Uuid\Uuid;

class InsuranceCompanyDTO
{
    public function __construct(
        public readonly ?Uuid $uuid,
        public readonly string $insuranceCompanyName,
        public readonly ?string $address,
        public readonly ?string $phone,
        public readonly ?string $email,
        public readonly ?string $website
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: isset($data['uuid']) ? Uuid::fromString($data['uuid']) : null,
            insuranceCompanyName: $data['insurance_company_name'],
            address: $data['address'] ?? null,
            phone: $data['phone'] ?? null,
            email: $data['email'] ?? null,
            website: $data['website'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid?->toString(),
            'insurance_company_name' => $this->insuranceCompanyName,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'website' => $this->website,
        ];
    }
}
