<?php

namespace App\DTOs;

use Ramsey\Uuid\Uuid;

class CauseOfLossDTO
{
    public function __construct(
        public readonly ?Uuid $uuid,
        public readonly string $causeOfLossName, 
        public readonly ?string $description,
        public readonly ?string $severity
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: isset($data['uuid']) ? Uuid::fromString($data['uuid']) : null,
            causeOfLossName: $data['cause_loss_name'], 
            description: $data['description'] ?? null,
            severity: $data['severity'] ?? 'low'
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid?->toString(),
            'cause_loss_name' => $this->causeOfLossName, 
            'description' => $this->description,
            'severity' => $this->severity,
        ];
    }

    public function getCauseOfLossName(): string
    {
        return $this->causeOfLossName; 
    }
}