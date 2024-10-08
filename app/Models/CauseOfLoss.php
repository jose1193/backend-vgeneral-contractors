<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CauseOfLoss extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'cause_loss_name',
        'description',
        'severity',
    ];

    public function claims()
    {
        return $this->belongsToMany(Claim::class, 'claim_cause_of_loss', 'claim_id', 'cause_of_loss_id');
    }

}
