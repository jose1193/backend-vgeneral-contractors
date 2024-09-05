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
        'envelope_id ',
        'status ',
    ];

    public function claim()
    {
        return $this->belongsTo(Claim::class);
    }

}
