<?php

namespace App\DTOs;

use Ramsey\Uuid\Uuid;

    class TypeDamageDTO
    {
        public function __construct(
        public readonly ?Uuid $uuid,
        public readonly string $typeDamageName,
        public readonly ?string $description,
        public readonly ?string $severity
        ) {}

        public static function fromArray(array $data): self
        {
        return new self(
            uuid: isset($data['uuid']) ? Uuid::fromString($data['uuid']) : null,
            typeDamageName: $data['type_damage_name'],
            description: $data['description'] ?? null,
            severity: $data['severity'] ?? 'low'
        );
        }

        public function toArray(): array
        {
        return [
            'uuid' => $this->uuid?->toString(),
            'type_damage_name' => $this->typeDamageName,
            'description' => $this->description,
            'severity' => $this->severity,
        ];
        }
    }
