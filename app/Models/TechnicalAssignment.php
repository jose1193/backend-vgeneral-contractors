<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TechnicalAssignment extends Model
{
    use HasFactory;

    protected $fillable = ['claim_id', 'technical_user_id', 'assignment_date', 'assignment_status'];

    public function claim()
    {
        return $this->belongsTo(Claim::class,'claim_id');
    }

    public function technicalUser()
    {
    return $this->belongsTo(User::class, 'technical_user_id');
    }

}
