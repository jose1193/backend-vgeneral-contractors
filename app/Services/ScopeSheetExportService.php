<?php

namespace App\Services;

use App\Interfaces\ScopeSheetExportRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Exception;

class ScopeSheetExportService
{
    protected $cacheTime = 720; // Cache duration in minutes

    protected $serviceData;
    protected $pdfService;
    protected $imageService;
    protected $s3Service;
    protected $transactionService;
    protected $cacheService;
    protected $cacheKeyList = 'scope_sheets_user_list_';
    public function __construct(
        ScopeSheetExportRepositoryInterface $serviceData,
        PdfService $pdfService,
        ImageService $imageService,
        S3Service $s3Service,
        TransactionService $transactionService,
        CacheService $cacheService
    ) {
        $this->serviceData = $serviceData;
        $this->pdfService = $pdfService;
        $this->imageService = $imageService;
        $this->s3Service = $s3Service;
        $this->transactionService = $transactionService;
        $this->cacheService = $cacheService; 
    }

    private function getUserId()
    {
        return Auth::id();
    }

    public function all()
    {
        $userId = $this->getUserId();
        $cacheKey = $this->cacheKeyList . $userId;

        // Attempt to retrieve data from cache
        return $this->cacheService->getCachedData($cacheKey, $this->cacheTime, function () use ($userId) {
            return $this->serviceData->getByUser(Auth::user());
        });
    }

    public function storeData(array $details)
    {
        return $this->transactionService->handleTransaction(function () use ($details) {
            $scopeSheet = $this->validateAndGetScopeSheet($details['scope_sheet_id']);
            $claim = $scopeSheet->claim;
            $clientNames = $this->getClientNames($claim);
            $fileName = $this->generateFileName($clientNames);
            $imageUrls = $this->prepareImageUrls($claim, $scopeSheet);
            $images = $this->imageService->fetchImagesAsync($imageUrls);
            $pdfContent = $this->generatePdfContent($claim, $scopeSheet, $images);

            try {
                $this->cleanupTemporaryFiles($this->getPresentationImages($scopeSheet));
            } finally {
                
                $s3Path = $this->s3Service->storeFileS3($pdfContent, 'public/scope_sheet_previews/'. $fileName);
                $scopeSheetExport = $this->storeScopeSheetExport($details['scope_sheet_id'], $s3Path);
                
                // Update cache
                $this->updateDataCache();
                
                return $scopeSheetExport;
            }
        }, 'storing scope sheet');
    }

    private function generatePdfContent($claim, $scopeSheet, array $images): string
    {
        $pdfData = $this->preparePdfData(
            $claim,
            $scopeSheet,
            $images['signature'] ?? '',
            $images['sellerSignature'] ?? null,
            $images['header'] ?? '',
            $images['footer'] ?? ''
        );

        return $this->pdfService->generatePdfContent('pdf_scope_sheet.scope_sheet_view', $pdfData);
    }

    private function validateAndGetScopeSheet($scopeSheetId)
    {
        $scopeSheet = $this->serviceData->getByScopeSheetId($scopeSheetId);
        if (!$scopeSheet) {
            throw new Exception('ScopeSheet with the given scope_sheet_id does not exist.');
        }
        return $scopeSheet;
    }

    private function prepareImageUrls($claim, $scopeSheet): array
    {
        $imageUrls = [
            'header' => 'https://vgeneralbucket.s3.amazonaws.com/public/pdf_images_body/header2.jpg',
            'footer' => 'https://vgeneralbucket.s3.amazonaws.com/public/pdf_images_body/footer-vgeneral-contractors.jpg',
            'signature' => $claim->signature->signature_path,
        ];

        $sellerSignaturePath = $this->getSellerSignaturePath($scopeSheet->generatedBy);
        if ($sellerSignaturePath) {
            $imageUrls['sellerSignature'] = $sellerSignaturePath;
        }

        return $imageUrls;
    }

    private function getSellerSignaturePath($seller)
    {
        if ($seller) {
            $sellerSignature = $seller->sellerSignature()->latest()->first();
            return $sellerSignature->signature_path ?? null;
        }
        return null;
    }

    private function storeScopeSheetExport($scopeSheetId, $s3Path)
    {
        return $this->serviceData->store([
            'uuid' => Uuid::uuid4()->toString(),
            'scope_sheet_id' => $scopeSheetId,
            'full_pdf_path' => $s3Path,
            'generated_by' => $this->getUserId()
        ]);
    }

    private function preparePdfData($claim, $scopeSheet, string $signatureImageBase64, ?string $sellerSignatureImage, string $headerImageBase64, string $footerImageBase64): array
    {
        return [
            'claim_id' => $claim->id,
            'scope_sheet_id' => $scopeSheet->id,
            'claim_names' => implode(', ', $this->getClientNamesArray($claim)),
            'property_address' => $claim->property->property_address,
            'property_state' => $claim->property->property_state,
            'property_city' => $claim->property->property_city,
            'postal_code' => $claim->property->property_postal_code,
            'property_country' => $claim->property->property_country,
            'claim_date' => $claim->created_at->format('Y-m-d'),
            'insurance_company' => $claim->insuranceCompanyAssignment->insuranceCompany->insurance_company_name,
            'policy_number' => $claim->policy_number,
            'date_of_loss' => $claim->date_of_loss,
            'claim_number' => $claim->claim_number,
            'cell_phone' => $this->getPrimaryCustomerData($claim)['cell_phone'],
            'home_phone' => $this->getPrimaryCustomerData($claim)['home_phone'],
            'email' => $this->getPrimaryCustomerData($claim)['email'],
            'signature_image' => $signatureImageBase64,
            'signature_name' => $claim->signature->user->name . ' ' . $claim->signature->user->last_name,
            'company_name' => $claim->signature->company_name,
            'company_name_uppercase' => strtoupper($claim->signature->company_name),
            'company_address' => $claim->signature->address,
            'company_email' => $claim->signature->email,
            'date' => now()->format('Y-m-d'),
            'cause_of_loss' => $claim->typeDamage->type_damage_name,
            'affected_areas' => $this->getAffectedAreas($scopeSheet),
            'presentation_images' => $this->getPresentationImages($scopeSheet),
            'zone_images' => $this->getZoneImages($scopeSheet),
            'headerImageBase64' => $headerImageBase64,
            'footerImageBase64' => $footerImageBase64,
            'requested_services' => $this->getRequestedServices($claim),
            'seller_signature_image' => $sellerSignatureImage,
        ];
    }

    private function cleanupTemporaryFiles(array $presentationImages): void
    {
        foreach ($presentationImages as $image) {
            $this->deleteFileIfExists($image['path']);
        }
    }

    private function deleteFileIfExists($filePath): void
    {
        if (isset($filePath) && file_exists($filePath)) {
            try {
                @unlink($filePath);
            } catch (\Exception $e) {
                Log::error('Error deleting temporary file', ['path' => $filePath, 'error' => $e->getMessage()]);
            }
        }
    }

    private function getClientNames($claim): string
    {
        return $claim->property->customers->map(fn($customer) => $this->sanitizeClientName($customer->name . ' ' . $customer->last_name))
            ->implode('_');
    }

    private function generateFileName(string $clientNames): string
    {
        return 'scope_sheet-' . $clientNames . '-' . now()->format('Y-m-d') . '.pdf';
    }

    private function getClientNamesArray($claim): array
    {
        return $claim->property->customers->map(fn($customer) => ucwords(strtolower($this->sanitizeClientName($customer->name . ' ' . $customer->last_name))))
            ->toArray();
    }

    private function getPrimaryCustomerData($claim): array
    {
        $primaryCustomerProperty = $claim->property->customerProperties->first(fn($customerProperty) => $customerProperty->isOwner());
        $customer = $primaryCustomerProperty ? $primaryCustomerProperty->customer : null;
        return [
            'cell_phone' => $customer->cell_phone ?? null,
            'home_phone' => $customer->home_phone ?? null,
            'email' => $customer->email ?? null,
        ];
    }

    private function getAffectedAreas($scopeSheet): string
    {
        $zoneNames = $scopeSheet->zones->pluck('zone.zone_name')->unique()->values();
        return $zoneNames->isEmpty() ? 'No affected areas specified' :
            ($zoneNames->count() === 1 ? $zoneNames->first() :
            $zoneNames->implode(', ', $zoneNames->count() - 1) . ' and ' . $zoneNames->last());
    }

    private function getRequestedServices($claim): string
    {
        $serviceNames = $claim->serviceRequests->pluck('requested_service')->unique()->values();
        return $serviceNames->isEmpty() ? 'No requested services specified' :
            ($serviceNames->count() === 1 ? $serviceNames->first() :
            $serviceNames->implode(', ', $serviceNames->count() - 1) . ' and ' . $serviceNames->last());
    }

    private function getPresentationImages($scopeSheet): array
    {
        return $scopeSheet->presentations->filter(fn($presentation) => $presentation->photo_path)
            ->sortBy(fn($presentation) => $presentation->photo_order)
            ->map(fn($presentation) => [
                'order' => $presentation->photo_order,
                'type' => $presentation->photo_type,
                'path' => $this->imageService->getImageBase64($presentation->photo_path)
            ])
            ->toArray();
    }

    private function getZoneImages($scopeSheet): array
    {
        return $scopeSheet->zones->sortBy('zone_order')->map(function ($zone) {
            return [
                'title' => $zone->zone->zone_name . ' ' . $zone->zone_order,
                'images' => $zone->photos->filter(fn($photo) => $photo->photo_path)
                    ->map(fn($photo) => [
                        'type' => $photo->photo_type,
                        'path' => $this->imageService->getImageBase64($photo->photo_path)
                    ])->toArray(),
                'notes' => $zone->zone_notes ?? 'No notes available',
            ];
        })->toArray();
    }

    private function sanitizeClientName(string $name): string
    {
        return preg_replace('/[^A-Za-z0-9\-]/', '', str_replace(' ', '-', strtolower($name)));
    }

    public function updateData(array $updateDetails, string $uuid)
    {
        return $this->transactionService->handleTransaction(function () use ($updateDetails, $uuid) {
            $existingScopeSheet = $this->serviceData->getByUuid($uuid);

            if (!$existingScopeSheet) {
                throw new \Exception("Scope sheet not found");
            }

            // Update the scope sheet in the database
            $updatedScopeSheet = $this->serviceData->update($updateDetails, $uuid);

            $this->updateDataCache();

            return $updatedScopeSheet;
        }, 'updating scope sheet');
    }

    public function showData(string $uuid)
    {
        $cacheKey = 'scope_sheet_export_' . $uuid;

        return $this->cacheService->getCachedData($cacheKey, $this->cacheTime, function () use ($uuid) {
            try {
                return $this->serviceData->getByUuid($uuid);
            } catch (Exception $e) {
                Log::error("Error fetching scope sheet with UUID {$uuid}: " . $e->getMessage(), [
                    'exception' => $e,
                    'stack_trace' => $e->getTraceAsString(),
                    'uuid' => $uuid
                ]);
                return null;
            }
        });
    }

    public function deleteData(string $uuid)
    {
        return $this->transactionService->handleTransaction(function () use ($uuid) {
            
            $cacheKey = 'scope_sheet_export_' . $uuid;
            $existingScopeSheet = $this->serviceData->getByUuid($uuid);

            if (!$existingScopeSheet) {
                throw new \Exception("Scope sheet not found");
            }

            // Delete the scope sheet from the database
            $this->serviceData->delete($uuid);

            // Delete the file from S3
            $filePath = 'public/scope_sheet_previews/' . $existingScopeSheet->file_name;
            $deleted = $this->s3Service->deleteFileFromStorage($filePath);

            if (!$deleted) {
                throw new \Exception("Failed to delete file from S3");
            }

            // Invalidate the cache
            $this->cacheService->invalidateCache($cacheKey);
            $this->updateDataCache();

        }, 'deleting scope sheet');
    }

        private function updateDataCache()
    {
        // Define the cache key and time
        $cacheKey = $this->cacheKeyList . $this->getUserId();
        $cacheTime = $this->cacheTime;

        // Update the data cache
        $this->cacheService->updateDataCache(
        $cacheKey,
        $cacheTime,
        function () {
            return $this->serviceData->getByUser(Auth::user());
        }
        );
    }

}
