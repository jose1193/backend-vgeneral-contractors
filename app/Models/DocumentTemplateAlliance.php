<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTemplateAlliance extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'template_name_alliance',
        'template_description_alliance',
        'template_type_alliance',
        'template_path_alliance',
        'uploaded_by',
        'alliance_company_id',
        'signature_path_id',
    ];

    /**
     * Obtiene el usuario que subió el documento.
     */
    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Obtiene la compañía de alianza asociada a este registro.
     * Asumiendo que tienes un modelo AllianceCompany.
     */
    public function allianceCompany()
    {
        return $this->belongsTo(AllianceCompany::class, 'alliance_company_id');
    }

    public function signature()
    {
        return $this->belongsTo(CompanySignature::class, 'signature_path_id');
    }
    
}
