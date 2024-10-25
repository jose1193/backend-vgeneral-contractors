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
    try {
        $localImagePath = storage_path('app/temp_signature.png');

        // Si la URL es local (comienza con /)
        if (str_starts_with($url, '/')) {
            $localPath = public_path(ltrim($url, '/'));
            if (file_exists($localPath)) {
                copy($localPath, $localImagePath);
                return $localImagePath;
            }
            Log::error('Local file not found', ['path' => $localPath]);
            return null;
        }

        // Si es una URL de S3
        if (str_contains($url, 's3.amazonaws.com')) {
            // Extraer la ruta relativa de S3
            $path = str_replace(
                ['https://', config('filesystems.disks.s3.url'), '//'], 
                ['', '', '/'], 
                $url
            );
            $path = ltrim($path, '/');

            // Usar Storage para S3
            $contents = Storage::disk('s3')->get($path);
            if ($contents) {
                file_put_contents($localImagePath, $contents);
                return $localImagePath;
            }
        }

        // Para cualquier otra URL
        $contents = file_get_contents($url);
        if ($contents !== false) {
            file_put_contents($localImagePath, $contents);
            return $localImagePath;
        }

        Log::error('Failed to download image', ['url' => $url]);
        return null;

    } catch (\Exception $e) {
        Log::error('Error downloading image: ' . $e->getMessage(), [
            'url' => $url,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return null;
    }
}
}