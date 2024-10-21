<?php

namespace App\Services;

use App\Interfaces\ClaimAgreementFullRepositoryInterface;
use Illuminate\Support\Str;
class FileManagementService
{
    public function __construct(
        private readonly S3Service $s3Service,
        private readonly ClaimAgreementFullRepositoryInterface $repository
    ) {}

    public function downloadTemplateFromS3($agreementType): string
    {
        // Always download the 'Agreement' template
        $documentTemplate = $this->repository->getByTemplateType('Agreement');
        
        $s3Url = $documentTemplate->template_path;
        $localTempPath = storage_path('app/temp_template.docx');
        file_put_contents($localTempPath, file_get_contents($s3Url));
        return $localTempPath;
    }

    public function generateFileName(string $clientNamesFile, string $agreementType, string $timestamp): string
    {
        $prefix = $agreementType === 'Agreement Full' ? 'agreement_full_' : 'agreement_preview_';
        $identifier = Str::slug($clientNamesFile, '_');
        return "{$prefix}{$identifier}_{$timestamp}.pdf";
    }

    public function cleanUpTempFiles(?string $localTempPath, ?string $processedWordPath): void
    {
        if ($localTempPath && file_exists($localTempPath)) {
            unlink($localTempPath);
        }
        if ($processedWordPath && file_exists($processedWordPath)) {
            unlink($processedWordPath);
        }
    }

    public function getFileForDownload(string $s3Path): StreamedResponse
    {
        if (!Storage::exists($s3Path)) {
            throw new \Exception("File not found: {$s3Path}");
        }

        $fileName = basename($s3Path);
        $mimeType = Storage::mimeType($s3Path);

        return Storage::response($s3Path, $fileName, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"'
        ]);
    }

    public function storeFileS3(string $fileContent, string $path): string
    {
        return $this->s3Service->storeFileS3($fileContent, $path);
    }

    public function deleteFileFromStorage(string $path): bool
    {
        return $this->s3Service->deleteFileFromStorage($path);
    }
}