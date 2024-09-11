<?php

namespace App\Interfaces;

interface DocuSignRepositoryInterface
{
    public function index();
    public function getByUuid(string $uuid);
    public function delete(string $uuid);
   
    public function connectDocusign(): array;
    public function callbackDocusign(string $code, string $token_uuid, int $userId);

    public function sendDocumentToDocusign(string $accessToken, array $envelopeDefinition): array;
    public function checkDocumentStatus(string $accessToken,string $envelopeId): array;
    public function getAllDocumentStatuses(string $accessToken);
}
