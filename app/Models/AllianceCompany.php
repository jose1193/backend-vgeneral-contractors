<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllianceCompany extends Model
{
    use HasFactory;
    protected $fillable = [
        'uuid',
        'alliance_company_name',
        'email',
        'phone',
        'address',
        'website',
        'user_id',
        
    ];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }

    public function claims()
    {
        return $this->belongsToMany(Claim::class, 'claim_alliances', 'alliance_company_id', 'claim_id')
                    ->withPivot('assignment_date'); 
    }

    public function documentTemplateAlliance()
    {
        return $this->hasMany(DocumentTemplateAlliance::class, 'uploaded_by');
    }

    
}
