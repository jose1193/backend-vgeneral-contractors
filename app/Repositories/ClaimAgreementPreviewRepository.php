<?php

namespace App\Repositories;
use App\Models\ClaimAgreementPreview;
use App\Models\Claim;
use App\Models\DocumentTemplate;
use App\Interfaces\ClaimAgreementPreviewRepositoryInterface;

class ClaimAgreementPreviewRepository implements ClaimAgreementPreviewRepositoryInterface
{
    /**
     * Create a new class instance.
     */
    public function index(){
         return ClaimAgreementPreview::orderBy('id', 'DESC')->get();
       
    }

     public function getByUuid(string $uuid)
    {
        return ClaimAgreementPreview::where('uuid', $uuid)->firstOrFail();
    }

    public function getClaimByUuid(string $uuid)
    {
    return Claim::with('property.customers')->where('uuid', $uuid)->firstOrFail();
    }


    public function store(array $data){
       return ClaimAgreementPreview::create($data);
    }

    public function update(array $data, $uuid)
   {
    $agreement = $this->getByUuid($uuid);
    
    $agreement->update($data);

    return $agreement;
 }
    
    public function delete(string $uuid)
    {
        $agreement = ClaimAgreementPreview::where('uuid', $uuid)->firstOrFail();
        $agreement->delete();
        return $agreement;
    }

   public function getClaimAgreementByUser($user)
    {
    if ($user->hasPermissionTo('Director Assistant', 'api')) {
        // Si el usuario tiene el permiso de "Super Admin", obtiene todos los archivos
        return ClaimAgreementPreview::orderBy('id', 'DESC')->get();
    } else {
        // Si el usuario no tiene permisos especiales, puede obtener los archivos que ha subido
        return ClaimAgreementPreview::where('generated_by', $user->id)
            ->orderBy('id', 'DESC')
            ->get();
    }

    }


    public function getByTemplateType(string $templateType)
    {
    return DocumentTemplate::where('template_type', $templateType)->firstOrFail();
    }

    
}

