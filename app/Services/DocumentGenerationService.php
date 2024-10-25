<?php

namespace App\Services;

use PhpOffice\PhpWord\TemplateProcessor;
use App\Interfaces\ClaimAgreementFullRepositoryInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DocumentGenerationService
{
    public function __construct(
        private readonly ClaimAgreementFullRepositoryInterface $repository,
        private readonly SignatureService $signatureService,
        private readonly FileManagementService $fileManagementService,
        private readonly NamingService $namingService,
        private readonly CustomerDataService $customerDataService
    ) {}

    public function generateDocumentAndStore($existingClaim, string $agreementType): array
    {
        try {
            $clientNamesFile = $this->namingService->getClientNamesFile($existingClaim);
            $primaryCustomerData = $this->customerDataService->getPrimaryCustomerData($existingClaim);
            
            $year = now()->format('Y');
            $month = now()->format('m');
            $timestamp = now()->format('YmdHis');
            
            $fileName = $this->fileManagementService->generateFileName($clientNamesFile, $agreementType, $timestamp);
            $folderType = $agreementType === 'Agreement Full' ? 'full' : 'preview';
            
            $s3FolderPath = "public/claim_agreements/{$folderType}/{$year}/{$month}/";

            // Always download the 'Agreement' template
            $localTempPath = $this->fileManagementService->downloadTemplateFromS3('Agreement');
            
            $processedWordPath = $this->processWordTemplate(
                $localTempPath,
                $existingClaim,
                $clientNamesFile,
                $primaryCustomerData,
                $agreementType
            );

            $fileContent = file_get_contents($processedWordPath);
            
            // Only store the document if it's 'Agreement Full' or 'Agreement'
            $s3Path = null;
            if ($agreementType === 'Agreement Full' || $agreementType === 'Agreement') {
                $s3Path = $this->fileManagementService->storeFileS3($fileContent, $s3FolderPath . $fileName);
            }

            $this->fileManagementService->cleanUpTempFiles($localTempPath, $processedWordPath);

            return ['local' => $localTempPath, 'processed' => $processedWordPath, 's3' => $s3Path];
        } catch (\Exception $e) {
            Log::error('Error generating document: ' . $e->getMessage(), [
                'claim_id' => $existingClaim->id ?? null,
                'agreement_type' => $agreementType,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function generateFileName(string $agreementType, $existingClaim, string $clientNamesFile, string $timestamp): string
    {
        $prefix = $agreementType === 'Agreement Full' ? 'agreement_full_' : 'agreement_preview_';
        
        if ($agreementType === 'Agreement Full') {
            $identifier = $existingClaim->id;
        } else {
            $identifier = Str::slug($clientNamesFile);
        }
        
        return "{$prefix}{$identifier}_{$timestamp}.pdf";
    }

    private function processWordTemplate(
        string $localTempPath,
        $existingClaim,
        string $clientNamesFile,
        array $primaryCustomerData,
        string $agreementType
    ): string {
        try {
            if (!file_exists($localTempPath)) {
                throw new \RuntimeException("Template file not found: $localTempPath");
            }

            Log::info('Processing template', [
                'template_path' => $localTempPath,
                'template_exists' => file_exists($localTempPath),
                'permissions' => substr(sprintf('%o', fileperms($localTempPath)), -4)
            ]);

            $templateProcessor = new TemplateProcessor($localTempPath);
            $values = $this->prepareTemplateValues($existingClaim, $clientNamesFile, $primaryCustomerData, $agreementType);

            $this->setTemplateValues($templateProcessor, $values);
            $this->ensureAllCustomerSignaturesReplaced($templateProcessor);

            $processedWordPath = storage_path('app/temp_processed.docx');
            $templateProcessor->saveAs($processedWordPath);

            if (!file_exists($processedWordPath)) {
                throw new \RuntimeException("Failed to create processed document: $processedWordPath");
            }

            return $processedWordPath;
        } catch (\Exception $e) {
            Log::error('Error processing template: ' . $e->getMessage(), [
                'template_path' => $localTempPath,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function setTemplateValues(TemplateProcessor $templateProcessor, array $values): void
    {
        foreach ($values as $key => $value) {
            try {
                if ($key === 'signature_image') {
                    $this->signatureService->processSignatureImage($templateProcessor, $value);
                } elseif (strpos($key, 'customer_signature_') === 0) {
                    $index = str_replace('customer_signature_', '', $key);
                    $signature = $values[$key];
                    $this->signatureService->processCustomerSignatures($templateProcessor, [$index => $signature]);
                } else {
                    // Sanitizar y convertir a string cualquier valor
                    $value = $this->sanitizeValue($value);
                    $templateProcessor->setValue($key, $value);
                }
            } catch (\Exception $e) {
                Log::error("Error setting template value for key '$key'", [
                    'value' => $value,
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }
    }

    private function sanitizeValue($value): string
{
    if (is_null($value)) return '';
    
    if (!is_string($value)) {
        $value = (string) $value;
    }
    
    // Convertir a ASCII y remover caracteres especiales
    $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);
    
    // Remover cualquier caracter que no sea alfanumérico, espacio o puntuación básica
    $value = preg_replace('/[^\p{L}\p{N}\s\-_.,()\'\"]/u', '', $value);
    
    // Eliminar múltiples espacios
    $value = preg_replace('/\s+/', ' ', $value);
    
    return trim($value);
}

    private function ensureAllCustomerSignaturesReplaced(TemplateProcessor $templateProcessor): void
    {
        for ($i = 0; $i < 5; $i++) {
            if (!isset($values["customer_signature_$i"])) {
                $templateProcessor->setValue("customer_signature_$i", '');
                $templateProcessor->setValue("customer_name_$i", '');
            }
        }
    }

    private function prepareTemplateValues(
        $existingClaim,
        string $clientNamesFile,
        array $primaryCustomerData,
        string $agreementType
    ): array {
        try {
            $customerSignatures = $agreementType === 'Agreement Full' 
                ? $this->signatureService->getCustomerSignatures($existingClaim, $agreementType) 
                : null;

            $values = [
                'claim_id' => $existingClaim->id,
                'claim_names' => implode(', ', $this->namingService->getClientNamesArray($existingClaim)),
                'property_address' => $existingClaim->property->property_address,
                'property_state' => $existingClaim->property->property_state,
                'property_city' => $existingClaim->property->property_city,
                'postal_code' => $existingClaim->property->property_postal_code,
                'property_country' => $existingClaim->property->property_country,
                'claim_date' => $existingClaim->created_at->format('m-d-Y'),
                'insurance_company' => $existingClaim->insuranceCompanyAssignment->insuranceCompany->insurance_company_name,
                'policy_number' => $existingClaim->policy_number,
                'date_of_loss' => $existingClaim->date_of_loss ?? '',
                'damage_description' => $existingClaim->damage_description ?? '',
                'scope_of_work' => $existingClaim->scope_of_work ?? '',
                'customer_reviewed' => $existingClaim->customer_reviewed ?? '',
                'description_of_loss' => $existingClaim->description_of_loss ?? '',
                'claim_number' => $existingClaim->claim_number ?? '',
                'cell_phone' => $this->formatPhoneNumber($primaryCustomerData['cell_phone'] ?? ''),
                'home_phone' => $this->formatPhoneNumber($primaryCustomerData['home_phone'] ?? ''),
                'email' => $primaryCustomerData['email'] ?? '',
                'occupation' => $primaryCustomerData['occupation'] ?? '',
                'primary_customer_initials' => ($primaryCustomerData['initials_first'] ?? '') . '/' . ($primaryCustomerData['initials_last'] ?? ''),
                'primary_customer_name' => $primaryCustomerData['full_name'] ?? '',
                'signature_image' => $existingClaim->signature->signature_path ?? '',
                'signature_name' => ($existingClaim->signature->user->name ?? '') . ' ' . ($existingClaim->signature->user->last_name ?? ''),
                'company_name' => $existingClaim->signature->company_name ?? '',
                'company_name_uppercase' => strtoupper($existingClaim->signature->company_name ?? ''),
                'company_address' => $existingClaim->signature->address ?? '',
                'company_email' => $existingClaim->signature->email ?? '',
                'date' => now()->format('m-d-Y'),
                
                // Valores del affidavit
                'affidavit_mortgage_company_name' => $existingClaim->affidavit->mortgage_company_name ?? '',
                'affidavit_mortgage_company_phone' => $existingClaim->affidavit->mortgage_company_phone ?? '',
                'affidavit_mortgage_loan_number' => $existingClaim->affidavit->mortgage_loan_number ?? '',
                'affidavit_description' => $existingClaim->affidavit->description ?? '',
                'affidavit_amount_paid' => $existingClaim->affidavit->amount_paid ?? '',
                'affidavit_day_of_loss_ago' => $existingClaim->affidavit->day_of_loss_ago ?? '',
            ];

            // Verificar si el claim tiene un affidavit
            if ($existingClaim->affidavit) {
                $values += [
                    'never_had_prior_loss' => $existingClaim->affidavit->never_had_prior_loss ? '[X]' : '[ ]',
                    'has_never_had_prior_loss' => $existingClaim->affidavit->has_never_had_prior_loss ? '[X]' : '[ ]',
                ];
            } else {
                $values += [
                    'never_had_prior_loss' => '☐',
                    'has_never_had_prior_loss' => '☐',
                ];
            }

            // Get requested services
            $requestedServices = $existingClaim->serviceRequests->map(function ($serviceRequest) {
                return [
                    'requested_service' => $serviceRequest->requested_service ?? '',
                ];
            })->toArray();

            // Add requested services as a comma-separated string
            $values['requested_services'] = implode(', ', array_column($requestedServices, 'requested_service'));

            // Add CEO initials
            $ceoInitials = $this->namingService->getInitials(
                ($existingClaim->signature->user->name ?? '') . ' ' . 
                ($existingClaim->signature->user->last_name ?? '')
            );
            $values['ceo_initials'] = $ceoInitials['first'] . '/' . $ceoInitials['last'];
        
            if ($customerSignatures) {
                foreach ($customerSignatures as $index => $signature) {
                    $values["customer_signature_$index"] = $signature;
                    $values["customer_name_$index"] = $signature ? $signature['name'] : '';
                }
            }

            // Sanitizar todos los valores antes de devolverlos
            return array_map([$this, 'sanitizeValue'], $values);
            
        } catch (\Exception $e) {
            Log::error('Error preparing template values: ' . $e->getMessage(), [
                'claim_id' => $existingClaim->id ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove all non-digit characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        // Remove leading '1' if present (for numbers starting with +1)
        if (strlen($cleaned) === 11 && substr($cleaned, 0, 1) === '1') {
            $cleaned = substr($cleaned, 1);
        }
        
        // Check if we have a 10-digit number
        if (strlen($cleaned) === 10) {
            return sprintf("(%s) %s-%s", 
                substr($cleaned, 0, 3),
                substr($cleaned, 3, 3),
                substr($cleaned, 6)
            );
        }
        
        // If it's not a 10-digit number, return the original input
        return $phoneNumber;
    }
}