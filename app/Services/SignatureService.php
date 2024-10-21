<?php

namespace App\Services;

use PhpOffice\PhpWord\TemplateProcessor;
use Illuminate\Support\Facades\Log;

class SignatureService
{
    public function processSignatureImage(TemplateProcessor $templateProcessor, string $value): void
    {
        $signatureImagePath = $this->downloadImageFromUrl($value);
        if ($signatureImagePath) {
            $templateProcessor->setImageValue('signature_image', [
                'path' => $signatureImagePath,
                'width' => 100,
                'height' => 100,
                'ratio' => true
            ]);
        } else {
            Log::error("Error: Failed to create signature image from provided data");
        }
    }

    public function processCustomerSignatures(TemplateProcessor $templateProcessor, array $signatures): void
    {
        foreach ($signatures as $index => $signature) {
            if ($signature) {
                $signatureImagePath = $this->downloadImageFromUrl($signature['signature_data']);
                if ($signatureImagePath) {
                    $templateProcessor->setImageValue("customer_signature_$index", [
                        'path' => $signatureImagePath,
                        'width' => 100,
                        'height' => 50,
                        'ratio' => true
                    ]);
                    $templateProcessor->setValue("customer_name_$index", $signature['name']);
                } else {
                    Log::error("Error: Failed to create customer signature image from provided data");
                    $templateProcessor->setValue("customer_signature_$index", '');
                    $templateProcessor->setValue("customer_name_$index", '');
                }
            } else {
                $templateProcessor->setValue("customer_signature_$index", '');
                $templateProcessor->setValue("customer_name_$index", '');
            }
        }
    }

    public function getCustomerSignatures($existingClaim, string $agreementType): ?array
    {
        if ($agreementType !== 'Agreement Full') {
            return null;
        }

        $signatures = [];
        $customers = $existingClaim->property->customers;

        foreach ($customers as $index => $customer) {
            $customerSignature = $customer->customerSignature()->latest()->first();

            if ($customerSignature) {
                $signatures[$index] = [
                    'name' => $customer->name . ' ' . $customer->last_name,
                    'signature_data' => $customerSignature->signature_data,
                ];
            } else {
                $signatures[$index] = null;
            }
        }

        return $signatures;
    }

    private function downloadImageFromUrl(string $url): ?string
    {
        $localImagePath = storage_path('app/temp_signature.png');
        $imageContents = file_get_contents($url);

        if ($imageContents !== false) {
            file_put_contents($localImagePath, $imageContents);
            return $localImagePath;
        }

        return null;
    }
}