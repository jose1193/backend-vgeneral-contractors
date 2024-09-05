<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimAlliance extends Model
{
    use HasFactory;
    protected $fillable = [
        'claim_id',
        'alliance_company_id',
        'assignment_date'
    ];


    public function claim()
    {
        return $this->belongsTo(Claim::class);
    }

    public function allianceCompany()
    {
        return $this->belongsTo(AllianceCompany::class);
    }
}
