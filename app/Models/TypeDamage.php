<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TypeDamage extends Model
{
    use HasFactory;
      protected $fillable = [
        'uuid',
        'type_damage_name',
        'description',
        'severity',
    ];
}
