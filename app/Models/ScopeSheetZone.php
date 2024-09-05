<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScopeSheetZone extends Model
{
    use HasFactory;

     protected $fillable = [
        'uuid',
        'scope_sheet_id',
        'zone_id',
        'zone_order',
        'zone_notes',
    ];

    public function scopeSheet()
    {
        return $this->belongsTo(ScopeSheet::class);
    }

    public function zone()
    {
        return $this->belongsTo(Zone::class);
    }

    public function photos()
    {
        return $this->hasMany(ScopeSheetZonePhoto::class);
    }

    
}
