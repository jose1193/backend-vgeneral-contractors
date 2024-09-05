<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileAssignmentEsx extends Model
{
    use HasFactory;

    protected $fillable = ['file_id', 'public_adjuster_id', 'assigned_by'];

    public function file()
    {
        return $this->belongsTo(FilesEsx::class, 'file_id');
    }

    public function publicAdjuster()
    {
        return $this->belongsTo(User::class, 'public_adjuster_id');
    }

    public function assigner()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
}
