<?php

declare(strict_types=1);

namespace App\DTOs;

use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Uuid;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class DocumentTemplateDTO
{
    public function __construct(
        public readonly ?UuidInterface $uuid,
        public readonly ?int $signaturePathId,
        public readonly ?string $templateName,
        public readonly ?string $templateDescription,
        public readonly ?string $templateType,
        public readonly UploadedFile|string|null $templatePath,
        public readonly ?int $uploadedBy
    ) {
        // Log de construcción
        Log::info('DocumentTemplateDTO constructed', [
            'uuid' => $this->uuid?->toString(),
            'signaturePathId' => $this->signaturePathId,
            'templateName' => $this->templateName,
            'templateDescription' => $this->templateDescription,
            'templateType' => $this->templateType,
            'hasTemplatePath' => $this->templatePath !== null,
            'uploadedBy' => $this->uploadedBy
        ]);
    }

    public static function fromArray(array $data): self
    {
        // Log de datos de entrada
        Log::info('Creating DocumentTemplateDTO from array', [
            'input_data' => $data
        ]);

        return new self(
            uuid: isset($data['uuid']) ? Uuid::fromString($data['uuid']) : null,
            signaturePathId: $data['signature_path_id'] ?? null,
            templateName: $data['template_name'] ?? null,
            templateDescription: $data['template_description'] ?? null,
            templateType: $data['template_type'] ?? null,
            templatePath: $data['template_path'] ?? null,
            uploadedBy: $data['uploaded_by'] ?? null
        );
    }

    public function toArray(): array
    {
        // Creamos el array base con todos los campos
        $array = [
            'template_name' => $this->templateName,
            'template_description' => $this->templateDescription,
            'template_type' => $this->templateType,
            'signature_path_id' => $this->signaturePathId
        ];

        // Solo agregamos template_path si existe
        if ($this->templatePath !== null) {
            $array['template_path'] = $this->templatePath;
        }

        // Solo agregamos uploaded_by si existe
        if ($this->uploadedBy !== null) {
            $array['uploaded_by'] = $this->uploadedBy;
        }

        // Solo agregamos uuid si existe
        if ($this->uuid !== null) {
            $array['uuid'] = $this->uuid->toString();
        }

        // Log antes de la transformación final
        Log::info('DocumentTemplateDTO toArray transformation', [
            'raw_array' => $array,
            'has_template_name' => isset($array['template_name']),
            'has_template_description' => isset($array['template_description']),
            'has_template_type' => isset($array['template_type']),
            'has_signature_path_id' => isset($array['signature_path_id']),
            'has_template_path' => isset($array['template_path']),
            'has_uploaded_by' => isset($array['uploaded_by']),
            'has_uuid' => isset($array['uuid'])
        ]);

        // Para actualizaciones, no filtramos los nulls para permitir actualizaciones explícitas
        return $array;
    }
}