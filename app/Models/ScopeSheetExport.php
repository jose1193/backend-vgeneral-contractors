<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScopeSheetExport extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'scope_sheet_id',
        'full_pdf_path',
        'generated_by',
    ];

    public function scopeSheet()
    {
        return $this->belongsTo(ScopeSheet::class);
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
