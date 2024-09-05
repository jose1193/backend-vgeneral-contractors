<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicAdjuster extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'user_id',
        'public_company_id',
        
    ];

     public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }


    
    public function publicCompany()
    {
        return $this->belongsTo(PublicCompany::class,'public_company_id');
    }

      public function publicAdjusterAssignments()
    {
        return $this->hasMany(PublicAdjusterAssignment::class);
    }


}
