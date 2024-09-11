<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocusignToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'access_token',
        'refresh_token',
        'expires_at',
        'email_docusign',
        'name',
        'first_name',
        'last_name',
        'connected_by',
    ];

    public function connectedBy()
    {
        return $this->belongsTo(User::class, 'connected_by');
    }
}
