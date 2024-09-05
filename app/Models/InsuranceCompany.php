<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceCompany extends Model
{
    use HasFactory;
    protected $fillable = [
        'uuid',
        'insurance_company_name',
        'address',
        'phone',
        'email',
        'website',
    ];

    public function insuranceAdjuster()
    {
        return $this->hasMany(InsuranceAdjuster::class,'insurance_company_id');
    }

    public function insuranceCompanyAssignments()
    {
        return $this->hasMany(InsuranceCompanyAssignment::class);
    }
}
