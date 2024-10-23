<?php

namespace App\Repositories;

use App\Models\DocumentTemplateAlliance;
use App\Models\CompanySignature;
use App\Models\User;
use App\Interfaces\DocumentTemplateAllianceRepositoryInterface;
use App\Interfaces\UsersRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DocumentTemplateAllianceRepository implements DocumentTemplateAllianceRepositoryInterface
{
    public function __construct(
        private readonly UsersRepositoryInterface $userRepository
    ) {}

    public function index(): Collection
    {
        return DocumentTemplateAlliance::orderBy('id', 'DESC')->get();
    }

    public function getByUuid(string $uuid): ?DocumentTemplateAlliance
    {
        return DocumentTemplateAlliance::where('uuid', $uuid)->firstOrFail();
    }


    public function getDocumentTemplateAlliancesByUser(object $user): Collection
    {
    
        if ($this->isSuperAdmin($user->id)) {
            return DocumentTemplateAlliance::orderBy('id', 'DESC')->get();
        }
        
        return DocumentTemplateAlliance::where('uploaded_by', $user->id)
            ->orderBy('id', 'DESC')
            ->get();
    }

    public function store(array $data): DocumentTemplateAlliance
    {
        return DocumentTemplateAlliance::create($data);
    }

    public function update(array $data, string $uuid): DocumentTemplateAlliance
    {
        $documentTemplate = $this->getByUuid($uuid);
        $documentTemplate->update($data);
        return $documentTemplate;
    }

    public function delete(string $uuid): bool
    {
        $documentTemplate = $this->getByUuid($uuid);
        return $documentTemplate->delete();
    }

    public function getByUploadedBy(int $uploadedBy): Collection
    {
        return DocumentTemplateAlliance::where('uploaded_by', $uploadedBy)
            ->orderBy('id', 'DESC')
            ->get();
    }

    public function isSuperAdmin(int $userId): bool
    {
        return $this->userRepository->isSuperAdmin($userId);
    }

    public function getAllSuperAdmins(): Collection
    {
        return $this->userRepository->getAllSuperAdmins();
    }

    public function templateTypeExistsForOtherTemplate(string $templateType, ?string $excludeUuid = null): bool
    {
        $query = DocumentTemplateAlliance::where('template_type_alliance', $templateType);

        if ($excludeUuid) {
            $query->where('uuid', '!=', $excludeUuid);
        }

        return $query->exists();
    }

    public function getCompanySignature(): ?object
    {
        return CompanySignature::firstOrFail();
    }

    public function existsByType(string $type): bool
    {
        return DocumentTemplateAlliance::query()
            ->where('template_type_alliance', $type)
            ->exists();
    }

    public function existsByName(string $name): bool
    {
        return DocumentTemplateAlliance::query()
            ->where('template_name_alliance', $name)
            ->exists();
    }

    public function existsByNameExcludingUuid(string $name, string $uuid): bool
    {
        return DocumentTemplateAlliance::query()
            ->where('template_name_alliance', $name)
            ->where('uuid', '!=', $uuid)
            ->exists();
    }
}