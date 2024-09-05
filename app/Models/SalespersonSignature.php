<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalespersonSignature extends Model
{
    use HasFactory;

     protected $fillable = [
        'uuid',
        'seller_id',
        'signature_path',
        'user_id_ref_by',
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function registeredBy()
    {
        return $this->belongsTo(User::class, 'user_id_ref_by'); // Relación con el usuario que registró la firma
    }
}
