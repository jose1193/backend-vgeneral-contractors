<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicCompany extends Model
{
    use HasFactory;
    protected $fillable = [
        'uuid',
        'public_company_name',
        'address',
        'phone',
        'email',
        'website',
        'unit',
    ];

     public function publicAdjuster()
    {
        return $this->hasMany(PublicAdjuster::class,'public_company_id');
    }

    public function publicCompanyAssignments()
    {
        return $this->hasMany(PublicCompanyAssignment::class);
    }
}
