<?php

namespace App\Repositories;

use App\Models\DocumentTemplate;
use App\Models\CompanySignature;
use App\Interfaces\DocumentTemplateRepositoryInterface;

class DocumentTemplateRepository implements DocumentTemplateRepositoryInterface
{
    /**
     * Get all document templates.
     */
    public function index()
    {
        return DocumentTemplate::with('uploaded_by')->orderBy('id', 'DESC')->get();
    }

    /**
     * Get a document template by its ID.
     */
    public function getByUuid(string $uuid)
    {
        return DocumentTemplate::where('uuid', $uuid)->firstOrFail();
    }

    /**
     * Store a new document template.
     */
    public function store(array $data)
    {
        return DocumentTemplate::create($data);
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
    public function getDocumentTemplatesByUser($user)
    {
    if ($user->hasPermissionTo('Super Admin', 'api')) {
        // If the user has "Super Admin" permission, get all templates
        return DocumentTemplate::orderBy('id', 'DESC')->get();
    } else {
        // If the user does not have special permissions, get templates created by the user
        return DocumentTemplate::where('uploaded_by', $user)
            ->orderBy('id', 'DESC')
            ->get();
    }
    }

    public function getCompanySignature()
    {
    return CompanySignature::firstOrFail();
                                 
    }

    

}
