<?php

namespace App\Services;

use App\Interfaces\ClaimAgreementFullRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;
use Exception;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\TemplateProcessor;

class ClaimAgreementFullService
{
    protected $serviceData;
    protected $cacheService;
    protected $s3Service;
    protected $transactionService;
    protected $cacheTime = 720;
    protected $cacheKeyList = 'claim_agreement_total_list_';

    public function __construct(
        ClaimAgreementFullRepositoryInterface $serviceData,
        CacheService $cacheService,
        S3Service $s3Service,
        TransactionService $transactionService
    ) {
        $this->serviceData = $serviceData;
        $this->cacheService = $cacheService;
        $this->s3Service = $s3Service;
        $this->transactionService = $transactionService;
       
    }

    private function getUserId()
    {
        return Auth::id();
    }

    public function all()
    {
        $userId = $this->getUserId();
        $cacheKey = $this->cacheKeyList . $userId;

        return $this->cacheService->getCachedData($cacheKey, $this->cacheTime, function () use ($userId) {
            return $this->serviceData->getClaimAgreementByUser(Auth::user());
        });
    }

    // STORE DATA
    public function storeData(array $details)
    {
        return $this->transactionService->handleTransaction(function () use ($details) {
        $filePaths = [];
        try {
            $existingClaim = $this->serviceData->getClaimByUuid($details['claim_uuid']);
            $filePaths = $this->generateDocumentAndStore($existingClaim, $details['agreement_type']);
            $full = $this->storeFullInDatabase($existingClaim, $filePaths['s3'], $details['agreement_type']);
            $this->updateDataCache();
            return $full;
        } catch (Exception $e) {
            Log::error('Error storing data: ' . $e->getMessage());
            throw $e;
        } finally {
            $this->cleanUpTempFiles($filePaths['local'] ?? null, $filePaths['processed'] ?? null);
        }
    });
    }

    private function generateDocumentAndStore($existingClaim, string $agreementType): array
    {
        $clientNamesFile = $this->getClientNamesFile($existingClaim);
        $primaryCustomerData = $this->getPrimaryCustomerData($existingClaim);
        $fileName = $this->generateFileName($clientNamesFile, $agreementType);

        $localTempPath = $this->downloadTemplateFromS3($agreementType);
        
        $processedWordPath = $this->processWordTemplate(
            $localTempPath,
            $existingClaim,
            $clientNamesFile,
            $primaryCustomerData,
            $agreementType
        );

        $fileContent = file_get_contents($processedWordPath);
        $s3Path = $this->s3Service->storeFileS3($fileContent, 'public/claim_agreement_fulls/' . $fileName);

        return ['local' => $localTempPath, 'processed' => $processedWordPath, 's3' => $s3Path];
    }

    private function getClientNamesFile($existingClaim): string
    {
        return collect($this->getClientNamesArray($existingClaim))->implode(' & ');
    }

    private function getPrimaryCustomerData($existingClaim): array
    {
    $primaryCustomerProperty = $existingClaim->property->customerProperties->first(fn($customerProperty) => $customerProperty->isOwner());

    return [
        'cell_phone' => $primaryCustomerProperty->customer->cell_phone ?? '',
        'home_phone' => $primaryCustomerProperty->customer->home_phone ?? '',
        'email' => $primaryCustomerProperty->customer->email ?? '',
        'occupation' => $primaryCustomerProperty->customer->occupation ?? '',
    ];
    }

    private function generateFileName(string $clientNamesFile, string $agreementType): string
    {
    $prefix = $agreementType === 'Agreement Full' ? 'agreement-full-' : 'agreement-';
    return $prefix . str_replace(' ', '_', strtolower($clientNamesFile)) . '-' . now()->format('Y-m-d') . '.docx';
    }
    private function downloadTemplateFromS3($agreementType): string
    {
        $documentTemplate = $this->serviceData->getByTemplateType($agreementType);
        
        $s3Url = $documentTemplate->template_path;

        $localTempPath = storage_path('app/temp_template.docx');
        file_put_contents($localTempPath, file_get_contents($s3Url));
        return $localTempPath;
    }

    private function processWordTemplate(
    string $localTempPath,
    $existingClaim,
    string $clientNamesFile,
    array $primaryCustomerData,
    string $agreementType
    ): string {
    $templateProcessor = new TemplateProcessor($localTempPath);
    $values = $this->prepareTemplateValues($existingClaim, $clientNamesFile, $primaryCustomerData, $agreementType);

    foreach ($values as $key => $value) {
        if ($key === 'signature_image') {
            $this->processSignatureImage($templateProcessor, $value);
        } elseif (strpos($key, 'customer_signature_') === 0) {
            $index = str_replace('customer_signature_', '', $key);
            $signature = $values[$key];
            $this->processCustomerSignatures($templateProcessor, [$index => $signature]);
        } else {
            $templateProcessor->setValue($key, $value);
        }
    }

    // Asegurarse de que todas las etiquetas de firma de cliente se reemplacen
    for ($i = 0; $i < 10; $i++) {  // Asumimos un máximo de 10 firmas de clientes
        if (!isset($values["customer_signature_$i"])) {
            $templateProcessor->setValue("customer_signature_$i", '');
            $templateProcessor->setValue("customer_name_$i", '');
        }
    }

    $processedWordPath = storage_path('app/temp_processed.docx');
    $templateProcessor->saveAs($processedWordPath);

    return $processedWordPath;
    }

    private function processSignatureImage(TemplateProcessor $templateProcessor, string $value): void
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

    private function processCustomerSignatures(TemplateProcessor $templateProcessor, array $signatures): void
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

    private function prepareTemplateValues(
    $existingClaim,
    string $clientNamesFile,
    array $primaryCustomerData,
    string $agreementType
    ): array {
    $customerSignatures = $agreementType === 'Agreement Full' ? $this->getCustomerSignatures($existingClaim, $agreementType) : null;

    $values = [
        'claim_id' => $existingClaim->id,
        'claim_names' => implode(', ', $this->getClientNamesArray($existingClaim)),
        'property_address' => $existingClaim->property->property_address,
        'property_state' => $existingClaim->property->property_state,
        'property_city' => $existingClaim->property->property_city,
        'postal_code' => $existingClaim->property->property_postal_code,
        'property_country' => $existingClaim->property->property_country,
        'claim_date' => $existingClaim->created_at->format('Y-m-d'),
        'insurance_company' => $existingClaim->insuranceCompanyAssignment->insuranceCompany->insurance_company_name,
        'policy_number' => $existingClaim->policy_number,
        'date_of_loss' => $existingClaim->date_of_loss,
        'damage_description' => $existingClaim->damage_description,
        'claim_number' => $existingClaim->claim_number,
        'cell_phone' => $primaryCustomerData['cell_phone'],
        'home_phone' => $primaryCustomerData['home_phone'],
        'email' => $primaryCustomerData['email'],
        'occupation' => $primaryCustomerData['occupation'],
        'signature_image' => $existingClaim->signature->signature_path,
        'signature_name' => $existingClaim->signature->user->name . ' ' . $existingClaim->signature->user->last_name,
        'company_name' => $existingClaim->signature->company_name,
        'company_name_uppercase' => strtoupper($existingClaim->signature->company_name),
        'company_address' => $existingClaim->signature->address,
        'company_email' => $existingClaim->signature->email,
        'date' => now()->format('Y-m-d'),
    ];

    if ($customerSignatures) {
        foreach ($customerSignatures as $index => $signature) {
            $values["customer_signature_$index"] = $signature;
            $values["customer_name_$index"] = $signature ? $signature['name'] : '';
        }
    }

        return $values;
    }

    private function getCustomerSignatures($existingClaim, string $agreementType): ?array
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


    private function getClientNamesArray($existingClaim): array
    {
        return $existingClaim->property->customers->map(function ($customer) {
            return ucwords(strtolower($this->sanitizeClientName($customer->name . ' ' . $customer->last_name)));
        })->toArray();
    }

    private function sanitizeClientName(string $clientName): string
    {
        return preg_replace('/[^A-Za-z0-9 ]/', '', $clientName);
    }

    private function storeFullInDatabase($existingClaim, string $s3Path, string $agreementType)
    {
    return $this->serviceData->store([
        'uuid' => Uuid::uuid4()->toString(),
        'claim_id' => $existingClaim->id,
        'full_pdf_path' => $s3Path,
        'generated_by' => $this->getUserId(),
        'agreement_type' => $agreementType,  // Include the agreement_type here
    ]);
    }


    private function cleanUpTempFiles(?string $localTempPath, ?string $processedWordPath): void
    {
        if ($localTempPath && file_exists($localTempPath)) {
            unlink($localTempPath);
        }
        if ($processedWordPath && file_exists($processedWordPath)) {
            unlink($processedWordPath);
        }
    }

    public function showData(string $uuid)
    {
        $cacheKey = 'claim_agreement_' . $uuid;

        return $this->cacheService->getCachedData($cacheKey, $this->cacheTime, function () use ($uuid) {
            try {
                return $this->serviceData->getByUuid($uuid);
            } catch (Exception $e) {
                Log::error('Error fetching claim agreement full: ' . $e->getMessage());
                return null;
            }
        });
    }

    public function updateData(array $updateDetails, string $uuid)
    {
        return $this->transactionService->handleTransaction(function () use ($updateDetails, $uuid) {
            $existingFull = $this->serviceData->getByUuid($uuid);

            if (!$existingFull) {
                throw new \Exception("Claim agreement not found");
            }

            $updateDetails['full_pdf_path'] = $updateDetails['full_pdf_path'] ?? $existingFull->full_pdf_path;
            $updateDetails['generated_by'] = $updateDetails['generated_by'] ?? $this->getUserId();

            $updatedFull = $this->serviceData->update($updateDetails, $uuid);
            $this->updateDataCache();

            return $updatedFull;
        });
    }

    public function deleteData(string $uuid)
    {
        return $this->transactionService->handleTransaction(function () use ($uuid) {
            $cacheKey = 'claim_agreement_' . $uuid;
            $existingFull = $this->serviceData->getByUuid($uuid);

            if (!$existingFull) {
                throw new \Exception("Claim agreement not found");
            }

            $this->serviceData->delete($uuid);
            $deleted = $this->s3Service->deleteFileFromStorage($existingFull->full_pdf_path);

            if (!$deleted) {
                throw new \Exception("Failed to delete file from S3");
            }

            $this->cacheService->invalidateCache($cacheKey);
            $this->updateDataCache();
        });
    }

    private function updateDataCache()
    {
        $userId = $this->getUserId();

        if (!$userId) {
            throw new \Exception('User ID is not available');
        }

        $cacheKey = $this->cacheKeyList . $userId;

        $this->cacheService->updateDataCache(
            $cacheKey,
            $this->cacheTime,
            function () {
                return $this->serviceData->getClaimAgreementByUser(Auth::user());
            }
        );
    }
}
