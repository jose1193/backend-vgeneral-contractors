<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceAdjuster extends Model
{
    use HasFactory;

     protected $fillable = [
        'uuid',
        'user_id',
        'insurance_company_id',
        
    ];

     public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }


    
    public function insuranceCompany()
    {
        return $this->belongsTo(InsuranceCompany::class,'insurance_company_id');
    }

     
  public function insuranceAdjusterAssignments()
    {
        return $this->hasMany(InsuranceAdjusterAssignment::class);
    }


}
