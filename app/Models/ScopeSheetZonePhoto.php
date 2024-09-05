<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScopeSheetZonePhoto extends Model
{
    use HasFactory;

     protected $fillable = [
        'uuid',
        'scope_sheet_zone_id',
        'photo_path',
        'photo_order',
    ];

     public function scopeSheetZone(): BelongsTo
    {
        return $this->belongsTo(ScopeSheetZone::class);
    }
}
