<?php

namespace App\DTOs;

class PasswordUpdateDTO
{
    public function __construct(
        public readonly string $currentPassword,
        public readonly string $newPassword
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            currentPassword: $data['current_password'],
            newPassword: $data['new_password']
        );
    }

    public function toArray(): array
    {
        return [
            'current_password' => $this->currentPassword,
            'new_password' => $this->newPassword,
        ];
    }
}