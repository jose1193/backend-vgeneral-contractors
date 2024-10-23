<?php

declare(strict_types=1);

namespace App\Services;

use App\Interfaces\DocumentTemplateAllianceRepositoryInterface;
use App\Interfaces\CompanySignatureRepositoryInterface;
use App\DTOs\DocumentTemplateAllianceDTO;
use App\Exceptions\UnauthorizedException;
use App\Exceptions\TemplateNotFoundException;
use Exception;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Psr\Log\LoggerInterface;

class DocumentTemplateAllianceService
{
    private const CACHE_TIME = 720; // minutes
    private const CACHE_KEY_LIST = 'document_template_alliances_user_list_';
    private const CACHE_KEY_TEMPLATE = 'document_template_alliance_';
    private const TEMPLATE_S3_PATH = 'public/document_template_alliances';
    private const SINGLE_INSTANCE_TYPES = ['Agreement', 'Agreement Full'];

    public function __construct(
        private readonly DocumentTemplateAllianceRepositoryInterface $repository,
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
            fn() => $this->repository->getDocumentTemplateAlliancesByUser(Auth::user())
        );
    }

    
    public function storeData(DocumentTemplateAllianceDTO $dto): object
    {
        return $this->transactionService->handleTransaction(function () use ($dto) {
            $user = $this->getAuthenticatedUser();
            
            // Validar nombre único
            if ($this->repository->existsByName($dto->templateNameAlliance)) {
                throw new \InvalidArgumentException(
                    "A template with name '{$dto->templateNameAlliance}' already exists"
                );
            }
            
            // Validar tipo único para Agreement y Agreement Full
            if (in_array($dto->templateTypeAlliance, self::SINGLE_INSTANCE_TYPES)) {
                if ($this->repository->existsByType($dto->templateTypeAlliance)) {
                    throw new \InvalidArgumentException(
                        "A template of type '{$dto->templateTypeAlliance}' already exists. Only one instance is allowed."
                    );
                }
            }
            
            $details = $this->prepareTemplateDetails($dto);
            $template = $this->createTemplate($details);

            $this->updateCaches(Auth::id(), Uuid::fromString($template->uuid));
            $this->logger->info('Document template alliance stored successfully', ['uuid' => $template->uuid]);
            return $template;
        }, 'storing document template alliance');
    }


    public function updateData(DocumentTemplateAllianceDTO $dto, UuidInterface $uuid): object
    {
        return $this->transactionService->handleTransaction(function () use ($dto, $uuid) {
            $existingTemplate = $this->getExistingTemplate($uuid);
            
            // Validar nombre único si se está actualizando
            if ($dto->templateNameAlliance !== null && 
                $dto->templateNameAlliance !== $existingTemplate->template_name_alliance) {
                
                if ($this->repository->existsByNameExcludingUuid($dto->templateNameAlliance, $uuid->toString())) {
                    throw new \InvalidArgumentException(
                        "A template with name '{$dto->templateNameAlliance}' already exists"
                    );
                }
            }
            
            // Validar tipo único para Agreement y Agreement Full
            if ($dto->templateTypeAlliance !== null && 
                in_array($dto->templateTypeAlliance, self::SINGLE_INSTANCE_TYPES) &&
                $dto->templateTypeAlliance !== $existingTemplate->template_type_alliance) {
                
                if ($this->repository->existsByType($dto->templateTypeAlliance)) {
                    throw new \InvalidArgumentException(
                        "A template of type '{$dto->templateTypeAlliance}' already exists. Only one instance is allowed."
                    );
                }
            }

            $updateDetails = [];

            if ($dto->templateNameAlliance !== null) {
                $updateDetails['template_name_alliance'] = $dto->templateNameAlliance;
            }
            if ($dto->templateDescriptionAlliance !== null) {
                $updateDetails['template_description_alliance'] = $dto->templateDescriptionAlliance;
            }
            if ($dto->templateTypeAlliance !== null) {
                $updateDetails['template_type_alliance'] = $dto->templateTypeAlliance;
            }
            if ($dto->signaturePathId !== null) {
                $updateDetails['signature_path_id'] = $dto->signaturePathId;
            }
            
            if ($dto->templatePathAlliance instanceof UploadedFile) {
                $updateDetails['template_path_alliance'] = $this->handleTemplatePathUpdate(
                    $dto->templatePathAlliance,
                    $existingTemplate->template_path_alliance
                );
            }

            $updateDetails['uploaded_by'] = Auth::id();
            $updateDetails['uuid'] = $uuid->toString();

            $this->logger->info('Attempting to update document template alliance with form data', [
                'template_uuid' => $uuid->toString(),
                'update_details' => $updateDetails,
                'user_id' => Auth::id(),
                'original_template_name' => $existingTemplate->template_name_alliance,
                'has_new_file' => isset($updateDetails['template_path_alliance']),
            ]);

            if (!empty($updateDetails)) {
                $template = $this->repository->update($updateDetails, $uuid->toString());
                $this->updateCaches(Auth::id(), $uuid);
                $this->logger->info('Document template alliance updated successfully', [
                    'uuid' => $uuid->toString(),
                    'updated_fields' => array_keys($updateDetails)
                ]);
                return $template;
            }

            return $existingTemplate;
        }, 'updating document template alliance');
    }


    private function handleTemplatePathUpdate($newTemplatePath, string $existingPath): string
    {
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
            $existingTemplate = $this->getExistingTemplate($uuid);

            $this->repository->delete($uuid->toString());
            $this->s3Service->deleteFileFromStorage($existingTemplate->template_path_alliance);

            $this->updateCaches(Auth::id(), $uuid);

            $this->logger->info('Document template alliance deleted successfully', ['uuid' => $uuid->toString()]);
            return true;
        }, 'deleting document template alliance');
    }

    private function getAuthenticatedUser()
    {
        $user = Auth::user();
        if (!$user) {
            throw new Exception("No authenticated user found");
        }
        return $user;
    }

    private function prepareTemplateDetails(DocumentTemplateAllianceDTO $dto): array
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
            throw new TemplateNotFoundException("Template alliance not found");
        }
        return $template;
    }

    private function updateCaches(int $userId, UuidInterface $uuid): void
    {
        $this->userCacheService->forgetUserListCache(self::CACHE_KEY_LIST, $userId);
        $this->userCacheService->forgetDataCacheByUuid(self::CACHE_KEY_TEMPLATE, $uuid->toString());
        $this->userCacheService->updateSuperAdminCaches(self::CACHE_KEY_LIST);
    }

    private function createTemplate(array $details): object
    {
        if (isset($details['template_path_alliance'])) {
            $details['template_path_alliance'] = $this->s3Service->storeAgreementFile(
                $details['template_path_alliance'], 
                self::TEMPLATE_S3_PATH
            );
        }
        return $this->repository->store($details);
    }
}