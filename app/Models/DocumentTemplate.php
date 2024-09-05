<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'signature_path_id',
        'template_name',
        'template_description',
        'template_type',
        'template_path',
        'uploaded_by',
    ];
    
     public function upload_by()
    {
    return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function signature()
    {
        return $this->belongsTo(CompanySignature::class, 'signature_path_id');
    }


}
