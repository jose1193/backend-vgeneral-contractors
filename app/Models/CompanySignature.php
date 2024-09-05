<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanySignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'company_name',
        'signature_path',
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

    public function claim()
    {
        return $this->hasMany(Claim::class,'signature_path_id');
    }

    public function documentTemplate()
    {
        return $this->hasMany(DocumentTemplate::class,'signature_path_id');
    }

    public function documentTemplateAlliance()
    {
        return $this->hasMany(DocumentTemplateAlliance::class,'signature_path_id');
    }

}
