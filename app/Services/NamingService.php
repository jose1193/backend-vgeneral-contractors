<?php

namespace App\Services;

class NamingService
{
    public function getClientNamesFile($existingClaim): string
    {
        return collect($this->getClientNamesArray($existingClaim))->implode(' & ');
    }

    public function getClientNamesArray($existingClaim): array
    {
        return $existingClaim->property->customers->map(function ($customer) {
            return ucwords(strtolower($this->sanitizeClientName($customer->name . ' ' . $customer->last_name)));
        })->toArray();
    }

    public function sanitizeClientName(string $clientName): string
    {
        return preg_replace('/[^A-Za-z0-9 ]/', '', $clientName);
    }

    public function getInitials(string $name): array
    {
        $words = explode(' ', $name);
        $initials = array_map(fn($word) => strtoupper(substr($word, 0, 1)), $words);
        
        // Ensure there are at least two initials
        while (count($initials) < 2) {
            $initials[] = '';
        }
        
        return [
            'first' => $initials[0],
            'last' => $initials[count($initials) - 1]
        ];
    }
}