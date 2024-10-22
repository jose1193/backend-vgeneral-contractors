<?php

namespace App\DTOs;

use Ramsey\Uuid\Uuid;
use Illuminate\Http\UploadedFile;
class DocumentTemplateDTO
{
    public function __construct(
        public readonly ?Uuid $uuid,
        public readonly ?int $signaturePathId,
        public readonly string $templateName,
        public readonly ?string $templateDescription,
        public readonly string $templateType,
        public readonly UploadedFile|string|null $templatePath,
        public readonly ?int $uploadedBy
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            uuid: isset($data['uuid']) ? Uuid::fromString($data['uuid']) : null,
            signaturePathId: $data['signature_path_id'] ?? null,
            templateName: $data['template_name'],
            templateDescription: $data['template_description'] ?? null,
            templateType: $data['template_type'],
            templatePath: $data['template_path'],
            uploadedBy: $data['uploaded_by'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid?->toString(),
            'signature_path_id' => $this->signaturePathId,
            'template_name' => $this->templateName,
            'template_description' => $this->templateDescription,
            'template_type' => $this->templateType,
            'template_path' => $this->templatePath,
            'uploaded_by' => $this->uploadedBy,
        ];
    }
}