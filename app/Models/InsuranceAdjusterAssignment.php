<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InsuranceAdjusterAssignment extends Model
{
    use HasFactory;

    protected $fillable = ['claim_id', 'insurance_adjuster_id', 'assignment_date'];

    public function claim()
    {
        return $this->belongsTo(Claim::class);
    }

    public function insuranceAdjuster()
    {
        return $this->belongsTo(User::class,'insurance_adjuster_id');
    }
}
