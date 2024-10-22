<?php

namespace App\Repositories;

use App\Models\DocumentTemplate;
use App\Models\User;
use App\Interfaces\DocumentTemplateRepositoryInterface;
use App\Interfaces\UsersRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DocumentTemplateRepository implements DocumentTemplateRepositoryInterface
{
    public function __construct(
        private readonly UsersRepositoryInterface $userRepository
    ) {}

    public function index(): Collection
    {
        return DocumentTemplate::orderBy('id', 'DESC')->get();
    }

    public function getByUuid(string $uuid): ?DocumentTemplate
    {
        return DocumentTemplate::where('uuid', $uuid)->firstOrFail();
    }

    public function getTemplatesByUser(object $user): Collection
    {
        if ($this->userRepository->isSuperAdmin($user->id)) {
            return DocumentTemplate::orderBy('id', 'DESC')->get();
        } else {
            return DocumentTemplate::where('uploaded_by', $user->id)
                ->orderBy('id', 'DESC')
                ->get();
        }
    }

    public function getByUploadedBy(int $uploadedBy)
    {
        return DocumentTemplate::where('uploaded_by', $uploadedBy)
            ->orderBy('id', 'DESC')
            ->first();
    }

    public function store(array $data): DocumentTemplate
    {
        return DocumentTemplate::create($data);
    }

    public function update(array $data, string $uuid): DocumentTemplate
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
        $query = DocumentTemplate::where('template_type', $templateType);

        if ($excludeUuid) {
            $query->where('uuid', '!=', $excludeUuid);
        }

        return $query->exists();
    }
}