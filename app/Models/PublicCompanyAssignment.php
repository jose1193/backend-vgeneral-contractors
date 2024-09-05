<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicCompanyAssignment extends Model
{
    use HasFactory;

    protected $fillable = ['claim_id', 'public_company_id', 'assignment_date'];

    public function claim()
    {
        return $this->belongsTo(Claim::class);
    }

    public function publicCompany()
    {
        return $this->belongsTo(PublicCompany::class);
    }
}
