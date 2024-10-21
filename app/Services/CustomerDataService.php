<?php

namespace App\Services;

class CustomerDataService
{
    public function __construct(
        private readonly NamingService $namingService
    ) {}

    public function getPrimaryCustomerData($existingClaim): array
    {
        $primaryCustomerProperty = $existingClaim->property->customerProperties->first(fn($customerProperty) => $customerProperty->isOwner());

        $fullName = $primaryCustomerProperty->customer->name . ' ' . $primaryCustomerProperty->customer->last_name;
        $initials = $this->namingService->getInitials($fullName);

        return [
            'full_name' => $fullName,
            'initials_first' => $initials['first'],
            'initials_last' => $initials['last'],
            'cell_phone' => $primaryCustomerProperty->customer->cell_phone ?? '',
            'home_phone' => $primaryCustomerProperty->customer->home_phone ?? '',
            'email' => $primaryCustomerProperty->customer->email ?? '',
            'occupation' => $primaryCustomerProperty->customer->occupation ?? '',
        ];
    }
}