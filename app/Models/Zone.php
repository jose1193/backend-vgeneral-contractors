<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Zone extends Model
{
    use HasFactory;
     use SoftDeletes;
     protected $fillable = ['uuid', 'zone_name'];

      public function scopeSheetZones()
    {
        return $this->hasMany(ScopeSheetZone::class);
    }
}
