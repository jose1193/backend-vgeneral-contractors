<?php

namespace App\Repositories;
use App\Models\DocumentTemplateAlliance;
use App\Models\CompanySignature;
use App\Interfaces\DocumentTemplateAllianceRepositoryInterface;


class DocumentTemplateAllianceRepository implements DocumentTemplateAllianceRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    /**
     * Get all document templates.
     */
    public function index()
    {
        return DocumentTemplateAlliance::with('uploaded_by')->orderBy('id', 'DESC')->get();
    }

    /**
     * Get a document template by its ID.
     */
    public function getByUuid(string $uuid)
    {
        return DocumentTemplateAlliance::where('uuid', $uuid)->firstOrFail();
    }

    /**
     * Store a new document template.
     */
    public function store(array $data)
    {
        return DocumentTemplateAlliance::create($data);
    }

    /**
     * Update an existing document template by its uuid.
     */
    public function update(array $data, string $uuid)
    {
        $documentTemplate = $this->getByUuid($uuid);
        $documentTemplate->update($data);
        return $documentTemplate;
    }

    /**
     * Delete a document template by its ID.
     */
    public function delete(string $id)
    {
        $documentTemplate = $this->getByUuid($id);
        $documentTemplate->delete();
        return $documentTemplate;
    }

    /**
     * Get document templates by user permissions.
     */
    public function getDocumentTemplateAlliancesByUser($user)
    {
    if ($user->hasPermissionTo('Super Admin', 'api')) {
        // If the user has "Super Admin" permission, get all templates
        return DocumentTemplateAlliance::orderBy('id', 'DESC')->get();
    } else {
        // If the user does not have special permissions, get templates created by the user
        return DocumentTemplateAlliance::where('uploaded_by', $user)
            ->orderBy('id', 'DESC')
            ->get();
    }
    }

    public function getCompanySignature()
    {
    return CompanySignature::firstOrFail();
                                 
    }

}
