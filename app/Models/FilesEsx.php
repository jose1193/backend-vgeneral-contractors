<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilesEsx extends Model
{
    use HasFactory;
    protected $fillable = [
        'uuid',
        'file_name',
        'file_path',
        'uploaded_by',
        
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function assignments()
    {
        return $this->hasMany(FileAssignmentEsx::class, 'file_id');
    }

    public function assignedAdjusters()
    {
        return $this->belongsToMany(User::class, 'file_assignment_esxes', 'file_id', 'public_adjuster_id');
    }

}
