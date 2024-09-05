<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScopeSheetPresentation extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'scope_sheet_id',
        'photo_type',
        'photo_order',
        'photo_path',
    ];

     public function scopeSheet()
    {
        return $this->belongsTo(ScopeSheet::class);
    }
}
