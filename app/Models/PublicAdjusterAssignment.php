<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PublicAdjusterAssignment extends Model
{
    use HasFactory;

    protected $fillable = ['claim_id', 'public_adjuster_id', 'assignment_date'];

    public function claim()
    {
        return $this->belongsTo(Claim::class);
    }

    public function publicAdjuster()
    {
        return $this->belongsTo(User::class,'public_adjuster_id');
    }

   
}
