<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    use HasFactory;
    protected $fillable = [
        'uuid',
        'property_address',
        'property_state',
        'property_city',
        'property_postal_code',
        'property_country',
        
    ];

   public function customers()
    {
        return $this->belongsToMany(Customer::class, 'customer_properties')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function claims() {
        return $this->hasMany(Claim::class);
    }

    // Este método es opcional, solo si necesitas acceder a la tabla pivot directamente
    public function customerProperties()
    {
        return $this->hasMany(CustomerProperty::class);
    }
}
