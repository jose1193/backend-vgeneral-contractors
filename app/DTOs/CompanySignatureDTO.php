<?php

namespace App\DTOs;

use Ramsey\Uuid\Uuid;

class CompanySignatureDTO
{
    public function __construct(
        public readonly ?Uuid $uuid,
        public readonly ?string $companyName,
        public readonly string $signaturePath,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $address,
        public readonly string $website,
        public readonly ?float $latitude,
        public readonly ?float $longitude,
        public readonly ?int $userId
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: isset($data['uuid']) ? Uuid::fromString($data['uuid']) : null,
            companyName: $data['company_name'] ?? null,
            signaturePath: $data['signature_path'],
            email: $data['email'],
            phone: $data['phone'],
            address: $data['address'],
            website: $data['website'],
            latitude: $data['latitude'] ?? null,
            longitude: $data['longitude'] ?? null,
            userId: $data['user_id'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid?->toString(),
            'company_name' => $this->companyName,
            'signature_path' => $this->signaturePath,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'website' => $this->website,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'user_id' => $this->userId,
            
        ];
    }
}