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
            $this->validateSuperAdminPermission();

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
            $this->validateSuperAdminPermission();
            $existingTemplate = $this->getExistingTemplate($uuid);

            $updateDetails = $this->prepareUpdateDetails($dto, $uuid, $existingTemplate);
            $template = $this->repository->update($updateDetails, $uuid->toString());

            $this->updateCaches(Auth::id(), $uuid);
            
            $this->logger->info('Document template updated successfully', ['uuid' => $uuid->toString()]);
            return $template;
        }, 'updating document template');
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
            $this->validateSuperAdminPermission();
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

    private function handleTemplatePathUpdate(string $newTemplatePath, string $existingPath): string
    {
        $this->s3Service->deleteFileFromStorage($existingPath);
        return $this->s3Service->storeFile($newTemplatePath, self::TEMPLATE_S3_PATH);
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
            $details['template_path'] = $this->s3Service->storeFile(
                $details['template_path'], 
                self::TEMPLATE_S3_PATH
            );
        }
        return $this->repository->store($details);
    }
}