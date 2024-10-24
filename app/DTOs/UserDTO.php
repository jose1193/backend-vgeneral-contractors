<?php

namespace App\DTOs;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;


class UserDTO
{
    public function __construct(
        public readonly ?UuidInterface $uuid,
        public readonly string $name,
        public readonly ?string $lastName,
        public readonly ?string $username,
        public readonly ?string $email,
        public readonly ?string $password,
        public readonly ?bool $generatePassword,
        public readonly ?string $phone,
        public readonly ?string $address,
        public readonly ?string $address2,
        public readonly ?string $zipCode,
        public readonly ?string $city,
        public readonly ?string $state,
        public readonly ?string $country,
        public readonly ?float $latitude,
        public readonly ?float $longitude,
        public readonly ?string $gender,
        public readonly ?int $userRole,  
        public readonly ?string $provider,
        public readonly ?string $providerId,
        public readonly ?string $providerAvatar,
        
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: isset($data['uuid']) ? Uuid::fromString($data['uuid']) : null,
            name: $data['name'],
            lastName: $data['last_name'] ?? null,
            username: $data['username'] ?? null,
            email: $data['email'] ?? null,
            password: $data['password'] ?? null,
            generatePassword: $data['generate_password'] ?? null,
            phone: $data['phone'] ?? null,
            address: $data['address'] ?? null,
            address2: $data['address_2'] ?? null,
            zipCode: $data['zip_code'] ?? null,
            city: $data['city'] ?? null,
            state: $data['state'] ?? null,
            country: $data['country'] ?? null,
            latitude: $data['latitude'] ?? null,
            longitude: $data['longitude'] ?? null,
            gender: $data['gender'] ?? null,
            userRole: $data['user_role'] ?? null,
            provider: $data['provider'] ?? null,
            providerId: $data['provider_id'] ?? null,
            providerAvatar: $data['provider_avatar'] ?? null,
            
        );
    }
    
    
    public function toArray(): array
    {
        return array_filter([
            'uuid' => $this->uuid?->toString(),
            'name' => $this->name,
            'last_name' => $this->lastName,
            'username' => $this->username,
            'email' => $this->email,
            'password' => $this->password,
            'generate_password' => $this->generatePassword,
            'phone' => $this->phone,
            'address' => $this->address,
            'address_2' => $this->address2,
            'zip_code' => $this->zipCode,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'gender' => $this->gender,
            'user_role' => $this->userRole,
            'provider' => $this->provider,
            'provider_id' => $this->providerId,
            'provider_avatar' => $this->providerAvatar,
        ], fn($value) => $value !== null);
    }

}