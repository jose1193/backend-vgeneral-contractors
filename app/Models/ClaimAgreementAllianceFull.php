<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimAgreementAllianceFull extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'alliance_company_id',
        'claim_id',
        'full_pdf_path',
        'agreement_type',
        'generated_by',
    ];

    public function claim()
    {
        return $this->belongsTo(Claim::class);
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function allianceCompany()
    {
        return $this->belongsTo(AllianceCompany::class);
    }
}
