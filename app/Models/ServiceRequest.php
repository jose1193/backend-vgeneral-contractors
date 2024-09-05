<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceRequest extends Model
{
    use HasFactory;

    protected $fillable = ['uuid', 'requested_service'];

    public function claims()
    {
        return $this->belongsToMany(Claim::class, 'claim_services', 'service_request_id', 'claim_id');
    }
}
