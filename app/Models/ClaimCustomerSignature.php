<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimCustomerSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'claim_id',
        'customer_id',
        'signature_data',
        'user_id_ref_by',
    ];

    public function claim()
    {
        return $this->belongsTo(Claim::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
