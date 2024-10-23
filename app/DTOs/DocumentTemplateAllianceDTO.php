<?php

namespace App\DTOs;

use Ramsey\Uuid\Uuid;
use Illuminate\Http\UploadedFile;

class DocumentTemplateAllianceDTO
{
    public function __construct(
        public readonly ?Uuid $uuid,
        public readonly string $templateNameAlliance,
        public readonly ?string $templateDescriptionAlliance,
        public readonly string $templateTypeAlliance,
        public readonly UploadedFile|string|null $templatePathAlliance,
        public readonly int $allianceCompanyId,
        public readonly ?int $uploadedBy
    ) {}

    public static function fromArray(array $data): self
    {
        // Validar si template_path_alliance es un archivo cargado
        $templatePath = $data['template_path_alliance'] ?? null;
        
        // Si es un array con la estructura de un archivo cargado, convertirlo a UploadedFile
        if (is_array($templatePath) && isset($templatePath['tmp_name'])) {
            $templatePath = new UploadedFile(
                $templatePath['tmp_name'],
                $templatePath['name'],
                $templatePath['type'],
                $templatePath['error'],
                true
            );
        }

        return new self(
            uuid: isset($data['uuid']) ? Uuid::fromString($data['uuid']) : null,
            templateNameAlliance: $data['template_name_alliance'],
            templateDescriptionAlliance: $data['template_description_alliance'] ?? null,
            templateTypeAlliance: $data['template_type_alliance'],
            templatePathAlliance: $templatePath,
            allianceCompanyId: $data['alliance_company_id'],
            uploadedBy: $data['uploaded_by'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid?->toString(),
            'template_name_alliance' => $this->templateNameAlliance,
            'template_description_alliance' => $this->templateDescriptionAlliance,
            'template_type_alliance' => $this->templateTypeAlliance,
            'template_path_alliance' => $this->templatePathAlliance, // Devolver el objeto completo
            'alliance_company_id' => $this->allianceCompanyId,
            'uploaded_by' => $this->uploadedBy
        ];
    }
}