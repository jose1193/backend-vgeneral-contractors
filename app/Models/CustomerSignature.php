<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerSignature extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'customer_id',
        'signature_data',
        'user_id_ref_by',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
