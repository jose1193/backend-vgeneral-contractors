<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClaimService extends Model
{
    use HasFactory;

   protected $fillable = ['claim_id', 'service_request_id'];

    
}
