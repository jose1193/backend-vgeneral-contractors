<?php

namespace App\Services;

use Psr\Log\LoggerInterface;

class S3SignatureService
{
    public function __construct(
        private readonly S3Service $s3Service,
        private readonly LoggerInterface $logger
    ) {}

    public function storeSignature(string $signatureData, string $s3Path): string
    {
        try {
            return $this->s3Service->storeSignatureInS3($signatureData, $s3Path);
        } catch (\Exception $e) {
            $this->logger->error('Failed to store signature in S3', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function deleteSignature(string $signaturePath): void
    {
        try {
            $this->s3Service->deleteFileFromStorage($signaturePath);
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete signature from S3', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function replaceSignature(string $oldSignaturePath, string $newSignatureData, string $s3Path): string
    {
        $this->deleteSignature($oldSignaturePath);
        return $this->storeSignature($newSignatureData, $s3Path);
    }
}