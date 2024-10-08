<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimStatu extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'claim_status_name',
        'background_color'
        
    ];

    public function claims()
    {
        return $this->hasMany(Claim::class, 'claim_status');
    }
}
