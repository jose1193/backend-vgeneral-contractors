<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AffidavitForm extends Model
{
    use HasFactory;
    protected $fillable = [
        'uuid',
        'claim_id',
        'day_of_loss_ago',
        'never_had_prior_loss',
        'has_never_had_prior_loss',
        'amount_paid',
        'description',
        'mortgage_company_name',
        'mortgage_company_phone',
        'mortgage_loan_number'
        
    ];

     public function claim()
    {
        return $this->belongsTo(Claim::class,'claim_id');
    }
}
