<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocusignClaim extends Model
{
    use HasFactory;
     protected $fillable = [
        'uuid',
        'claim_id',
        'envelope_id',
        'generated_by',
        
    ];

    public function claim()
    {
        return $this->belongsTo(Claim::class);
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

}
