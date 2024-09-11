<?php

namespace App\Services;

use App\Interfaces\DocuSignRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use Exception;
use Illuminate\Support\Facades\Http;

class DocuSignService
{
    protected $cacheTime = 720;
    protected $cacheKeyList = 'docusign_documents_user_list_';
    protected $docuSignRepository;
    protected $transactionService;
    protected $cacheService;

    public function __construct(
        DocuSignRepositoryInterface $serviceData,
        TransactionService $transactionService,
        CacheService $cacheService
    ) {
        $this->serviceData = $serviceData;
        $this->transactionService = $transactionService;
        $this->cacheService = $cacheService;
    }

    public function connectDocusign(): array
    {
        return $this->serviceData->connectDocusign();
    }

    public function callbackData(string $code): array
    {
        $token_uuid = Uuid::uuid4()->toString();
        $userId = Auth::id();
        return $this->serviceData->callbackDocusign($code, $token_uuid, $userId);
    }

    public function validateDocument(array $validatedData): array
    {
        return $this->transactionService->handleTransaction(function () use ($validatedData) {
        $userId = Auth::id();
        $token = $this->serviceData->getAccessToken($userId);

        if (!$token) {
            return ['message' => 'No DocuSign connection found. Please connect to DocuSign first.'];
        }

        $accessToken = $this->refreshAccessToken($token);
        $claim = $this->serviceData->getClaimByUuid($validatedData['claim_uuid']);
        $claimAgreement = $claim->claimAgreement->first();

        if (!$claimAgreement) {
            return ['message' => 'No claim agreement found for the given UUID.'];
        }

        if ($claim->claimDocusign->first()) {
            return ['message' => 'A Docusign record already exists for this claim.'];
        }

        $documentContent = $this->downloadDocumentFromS3($claimAgreement->full_pdf_path);
        $envelopeDefinition = $this->prepareEnvelopeDefinition($claim, $documentContent, $claimAgreement);

        $response = $this->serviceData->sendDocumentToDocusign($accessToken, $envelopeDefinition);

        $this->serviceData->storeDocusign([
            'uuid' => Uuid::uuid4()->toString(),
            'claim_id' => $claim->id,
            'envelope_id' => $response['envelope_id'],
            'generated_by' => $userId
        ]);

            return $response;
        }, 'validating document with DocuSign');
    }


    public function refreshAccessToken(string $refreshToken): string
    {
        $config = $this->serviceData->getDocusignConfig();
        $response = Http::withBasicAuth($config['client_id'], $config['client_secret'])
            ->post($config['api_auth_url'] . '/token', [
                'grant_type' => 'refresh_token',
                'refresh_token' => $refreshToken,
            ]);

        if ($response->successful()) {
            $result = $response->json();
            $this->serviceData->updateTokens(Auth::id(), $result);
            return $result['access_token'];
        }

        throw new Exception('Failed to refresh access token: ' . json_encode($response->json()));
    }

    public function all(): array
    {
        $userId = Auth::id();
        $cacheKey = $this->cacheKeyList . $userId;

        return $this->cacheService->getCachedData($cacheKey, $this->cacheTime, function () {
            return $this->serviceData->getByUser(Auth::user());
        });
    }

    public function getAllDocuments(): array
    {
        $refreshToken = $this->serviceData->getAccessToken(Auth::id());

        if (!$refreshToken) {
            return ['message' => 'No DocuSign connection found. Please connect to DocuSign first.'];
        }

        $accessToken = $this->refreshAccessToken($refreshToken);
        return $this->serviceData->getAllDocumentStatuses($accessToken);
    }

    public function checkDocumentStatusData(string $envelopeId): array
    {
        $accessToken = $this->refreshAccessToken($this->serviceData->getAccessToken(Auth::id()));
        return $this->serviceData->checkDocumentStatus($accessToken, $envelopeId);
    }

    public function deleteData(string $uuid): void
    {
        $this->transactionService->handleTransaction(function () use ($uuid) {
            $this->serviceData->delete($uuid);
        });
        $this->updateDataCache();
    }

    private function updateDataCache(): void
    {
        $cacheKey = $this->cacheKeyList . Auth::id();
        $this->cacheService->updateDataCache(
            $cacheKey,
            $this->cacheTime,
            fn() => $this->serviceData->getByUser(Auth::user())
        );
    }

    private function downloadDocumentFromS3(string $url): string
    {
        $response = Http::get($url);

        if ($response->successful()) {
            return $response->body();
        }

        throw new Exception('Failed to download document from S3');
    }

    private function prepareEnvelopeDefinition($claim, $documentContent, $claimAgreement): array
    {
        $primaryCustomerData = $this->getPrimaryCustomerData($claim);
        $fileBase64 = base64_encode($documentContent);
        $clientNamesFile = $this->getClientNamesFile($claim);
        $fileName = $this->generateFileName($clientNamesFile, $claimAgreement->agreement_type);

        $document = [
            'documentBase64' => $fileBase64,
            'name' => 'Docusign_Agreement_Full_' . $fileName,
            'fileExtension' => 'docx',
            'documentId' => '1'
        ];

        $recipient = [
            'email' => $primaryCustomerData['email'],
            'name' => $primaryCustomerData['name'],
            'recipientId' => '1',
            'routingOrder' => '1',
            'deliveryMethod' => 'email',
            'roleName' => 'Viewer',
            'recipientType' => 'certifiedDelivery'
        ];

        return [
            'emailSubject' => 'Document validation in progress - ' . $primaryCustomerData['name'],
            'documents' => [$document],
            'recipients' => [
                'certifiedDeliveries' => [$recipient],
            ],
            'status' => 'sent',
        ];
    }

    private function getPrimaryCustomerData($claim): array
    {
        $primaryCustomerProperty = $claim->property->customerProperties->first(fn($customerProperty) => $customerProperty->isOwner());

        return [
            'cell_phone' => $primaryCustomerProperty->customer->cell_phone ?? '',
            'home_phone' => $primaryCustomerProperty->customer->home_phone ?? '',
            'email' => $primaryCustomerProperty->customer->email ?? '',
            'occupation' => $primaryCustomerProperty->customer->occupation ?? '',
            'name' => ucwords(strtolower($this->sanitizeClientName($primaryCustomerProperty->customer->name . ' ' . $primaryCustomerProperty->customer->last_name)))
        ];
    }

    private function getClientNamesFile($claim): string
    {
        return collect($this->getClientNamesArray($claim))->implode(' & ');
    }

    private function getClientNamesArray($claim): array
    {
        if (!$claim->property || !$claim->property->customers) {
            return [];
        }

        return $claim->property->customers->map(function ($customer) {
            return ucwords(strtolower($this->sanitizeClientName($customer->name . ' ' . $customer->last_name)));
        })->toArray();
    }

    private function sanitizeClientName(string $clientName): string
    {
        return preg_replace('/[^A-Za-z0-9 ]/', '', $clientName);
    }

    private function generateFileName(string $clientNamesFile): string
    {
        return str_replace(' ', '_', strtolower($clientNamesFile)) . '-' . now()->format('Y-m-d');
    }
}