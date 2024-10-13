<?php

namespace App\DTOs;

class SocialLoginDTO
{
    public function __construct(
        public readonly string $provider,
        public readonly string $access_provider_token
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            provider: $data['provider'],
            access_provider_token: $data['access_provider_token']
        );
    }

    public function toArray(): array
    {
        return [
            'provider' => $this->provider,
            'access_provider_token' => $this->access_provider_token,
        ];
    }
}