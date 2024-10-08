<?php

namespace App\DTOs;

use Ramsey\Uuid\Uuid;

class ClaimStatusDTO
{
    public function __construct(
        public readonly ?Uuid $uuid,
        public readonly string $claimStatusName,
        public readonly ?string $backgroundColor
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: isset($data['uuid']) ? Uuid::fromString($data['uuid']) : null,
            claimStatusName: $data['claim_status_name'],
            backgroundColor: $data['background_color'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid?->toString(),
            'claim_status_name' => $this->claimStatusName,
            'background_color' => $this->backgroundColor,
        ];
    }

    
}