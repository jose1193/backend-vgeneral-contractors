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
        'user_id'
    ];

    public function insuranceAdjuster()
    {
        return $this->hasMany(InsuranceAdjuster::class,'insurance_company_id');
    }

    public function insuranceCompanyAssignments()
    {
        return $this->hasMany(InsuranceCompanyAssignment::class);
    }

    public function alliances()
    {
        return $this->belongsToMany(AllianceCompany::class, 'alliance_prohibited_insurances', 'insurance_company_id', 'alliance_company_id');
    }

     public function user()
    {
        return $this->belongsTo(User::class);
    }
}
