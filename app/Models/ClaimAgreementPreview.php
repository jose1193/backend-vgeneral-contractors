<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimAgreementPreview extends Model
{
    use HasFactory;
    protected $fillable = [
        'uuid',
        'claim_id',
        'preview_pdf_path',
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

}
