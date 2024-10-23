<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\DocumentTemplateRepositoryInterface;
use App\Interfaces\CompanySignatureRepositoryInterface;
use App\DTOs\DocumentTemplateDTO;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\TemplateNotFoundException;
use Exception;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

class DocumentTemplateService
{
    private const CACHE_TIME = 720; // minutes
    private const CACHE_KEY_LIST = 'document_templates_user_list_';
    private const CACHE_KEY_TEMPLATE = 'document_template_';
    private const TEMPLATE_S3_PATH = 'public/document_templates';
    private const SINGLE_INSTANCE_TYPES = ['Agreement', 'Agreement Full'];

    public function __construct(
        private readonly DocumentTemplateRepositoryInterface $repository,
         private readonly CompanySignatureRepositoryInterface $companySignatureRepository,
        private readonly S3Service $s3Service,
        private readonly TransactionService $transactionService,
        private readonly UserCacheService $userCacheService,
        private readonly LoggerInterface $logger
    ) {}

    public function all(): Collection
    {
        return $this->userCacheService->getCachedUserList(
            self::CACHE_KEY_LIST,
            Auth::id(),
            fn() => $this->repository->getTemplatesByUser(Auth::user())
        );
    }

    public function storeData(DocumentTemplateDTO $dto): object
    {
        return $this->transactionService->handleTransaction(function () use ($dto) {
            $user = $this->getAuthenticatedUser();
            
            // Validar nombre único
            if ($this->repository->existsByName($dto->templateName)) {
                throw new \InvalidArgumentException(
                    "A template with name '{$dto->templateName}' already exists"
                );
            }
            
            // Validar tipo único para Agreement y Agreement Full
            if (in_array($dto->templateType, self::SINGLE_INSTANCE_TYPES)) {
                if ($this->repository->existsByType($dto->templateType)) {
                    throw new \InvalidArgumentException(
                        "A template of type '{$dto->templateType}' already exists. Only one instance is allowed."
                    );
                }
            }

            $details = $this->prepareTemplateDetails($dto);
            $template = $this->createTemplate($details);

            $this->updateCaches(Auth::id(), Uuid::fromString($template->uuid));
            $this->logger->info('Document template stored successfully', ['uuid' => $template->uuid]);
            return $template;
        }, 'storing document template');
    }

     public function updateData(DocumentTemplateDTO $dto, UuidInterface $uuid): object
    {
        return $this->transactionService->handleTransaction(function () use ($dto, $uuid) {
            $existingTemplate = $this->getExistingTemplate($uuid);
            
            // Validar nombre único si se está actualizando
            if ($dto->templateName !== null && 
                $dto->templateName !== $existingTemplate->template_name) {
                
                if ($this->repository->existsByNameExcludingUuid($dto->templateName, $uuid->toString())) {
                    throw new \InvalidArgumentException(
                        "A template with name '{$dto->templateName}' already exists"
                    );
                }
            }
            
            // Validar tipo único para Agreement y Agreement Full
            if ($dto->templateType !== null && 
                in_array($dto->templateType, self::SINGLE_INSTANCE_TYPES) &&
                $dto->templateType !== $existingTemplate->template_type) {
                
                if ($this->repository->existsByType($dto->templateType)) {
                    throw new \InvalidArgumentException(
                        "A template of type '{$dto->templateType}' already exists. Only one instance is allowed."
                    );
                }
            }

            $updateDetails = [];

            if ($dto->templateName !== null) {
                $updateDetails['template_name'] = $dto->templateName;
            }
            if ($dto->templateDescription !== null) {
                $updateDetails['template_description'] = $dto->templateDescription;
            }
            if ($dto->templateType !== null) {
                $updateDetails['template_type'] = $dto->templateType;
            }
            if ($dto->signaturePathId !== null) {
                $updateDetails['signature_path_id'] = $dto->signaturePathId;
            }
            
            if ($dto->templatePath instanceof UploadedFile) {
                $updateDetails['template_path'] = $this->handleTemplatePathUpdate(
                    $dto->templatePath,
                    $existingTemplate->template_path
                );
            }

            $updateDetails['uploaded_by'] = Auth::id();
            $updateDetails['uuid'] = $uuid->toString();

            $this->logger->info('Attempting to update document template with form data', [
                'template_uuid' => $uuid->toString(),
                'update_details' => $updateDetails,
                'user_id' => Auth::id(),
                'original_template_name' => $existingTemplate->template_name,
                'has_new_file' => isset($updateDetails['template_path']),
            ]);

            if (!empty($updateDetails)) {
                $template = $this->repository->update($updateDetails, $uuid->toString());
                $this->updateCaches(Auth::id(), $uuid);
                $this->logger->info('Document template updated successfully', [
                    'uuid' => $uuid->toString(),
                    'updated_fields' => array_keys($updateDetails)
                ]);
                return $template;
            }

            return $existingTemplate;
        }, 'updating document template');
    }

    private function handleTemplatePathUpdate($newTemplatePath, string $existingPath): string
    {
        // Solo eliminar el archivo existente si el nuevo archivo es válido
        if ($newTemplatePath instanceof UploadedFile) {
            $this->s3Service->deleteFileFromStorage($existingPath);
            return $this->s3Service->storeAgreementFile($newTemplatePath, self::TEMPLATE_S3_PATH);
        }
        
        return $existingPath;
    }

    public function showData(UuidInterface $uuid): ?object
    {
        return $this->userCacheService->getCachedItem(
            self::CACHE_KEY_TEMPLATE,
            $uuid->toString(),
            fn() => $this->repository->getByUuid($uuid->toString())
        );
    }

    public function deleteData(UuidInterface $uuid): bool
    {
        return $this->transactionService->handleTransaction(function () use ($uuid) {
            //$this->validateSuperAdminPermission();
            $existingTemplate = $this->getExistingTemplate($uuid);

            $this->repository->delete($uuid->toString());
            $this->s3Service->deleteFileFromStorage($existingTemplate->template_path);

            $this->updateCaches(Auth::id(), $uuid);

            $this->logger->info('Document template deleted successfully', ['uuid' => $uuid->toString()]);
            return true;
        }, 'deleting document template');
    }

    private function getAuthenticatedUser()
    {
        $user = Auth::user();
        if (!$user) {
            throw new Exception("No authenticated user found");
        }
        return $user;
    }

    private function validateSuperAdminPermission(): void
    {
        $user = Auth::user();
        if (!$user || !$this->repository->isSuperAdmin(Auth::id())) {
            throw new UnauthorizedException("Unauthorized access. Only super admins can perform this operation.");
        }
    }

    private function prepareTemplateDetails(DocumentTemplateDTO $dto): array
    {
        $companySignature = $this->companySignatureRepository->findFirst();
        
        return [
            ...$dto->toArray(),
            'uuid' => Uuid::uuid4()->toString(),
            'uploaded_by' => Auth::id(),
            'signature_path_id' => $companySignature->id,
        ];
    }

    private function getExistingTemplate(UuidInterface $uuid): object
    {
        $template = $this->repository->getByUuid($uuid->toString());
        if (!$template) {
            throw new TemplateNotFoundException("Template not found");
        }
        return $template;
    }

    private function prepareUpdateDetails(DocumentTemplateDTO $dto, UuidInterface $uuid, object $existingTemplate): array
    {
        $updateDetails = $dto->toArray();
        $updateDetails['uuid'] = $uuid->toString();
        $updateDetails['uploaded_by'] = Auth::id();

        if ($dto->templatePath !== null) {
            $updateDetails['template_path'] = $this->handleTemplatePathUpdate(
                $dto->templatePath, 
                $existingTemplate->template_path
            );
        }

        return $updateDetails;
    }

    

    private function updateCaches(int $userId, UuidInterface $uuid): void
    {
        $this->userCacheService->forgetUserListCache(self::CACHE_KEY_LIST, $userId);
        $this->userCacheService->forgetDataCacheByUuid(self::CACHE_KEY_TEMPLATE, $uuid->toString());
        $this->userCacheService->updateSuperAdminCaches(self::CACHE_KEY_LIST);
    }

    private function createTemplate(array $details): object
    {
        if (isset($details['template_path'])) {
            $details['template_path'] = $this->s3Service->storeAgreementFile(
                $details['template_path'], 
                self::TEMPLATE_S3_PATH
            );
        }
        return $this->repository->store($details);
    }
}