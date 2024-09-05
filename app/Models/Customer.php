<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Customer extends Model
{
    use HasFactory;
    use SoftDeletes;


    protected $fillable = [
        'uuid',
        'name',
        'last_name',
        'email',
        'cell_phone',
        'home_phone',
        'occupation',
        'user_id',
        
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


     public function properties()
    {
        return $this->belongsToMany(Property::class, 'customer_properties')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function signatures()
    {
        return $this->hasMany(ClaimCustomerSignature::class);
    }

    public function customerSignature()
    {
        return $this->hasOne(CustomerSignature::class);
    }
}
