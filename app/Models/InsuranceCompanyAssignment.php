<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceCompanyAssignment extends Model
{
    use HasFactory;
     protected $fillable = [
        
        'claim_id',
        'insurance_company_id',
        'assignment_date',
    ];

     public function claim()
    {
        return $this->belongsTo(Claim::class);
    }

    public function insuranceCompany()
    {
        return $this->belongsTo(InsuranceCompany::class);
    }
}
