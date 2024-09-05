<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerProperty extends Model
{
    use HasFactory;

    // Definir la tabla asociada al modelo si el nombre no sigue la convención de Laravel
    protected $table = 'customer_properties';

    // Los campos que se pueden llenar masivamente
    protected $fillable = [
        'role',
        'property_id',
        'customer_id',
    ];

    // Definir las relaciones si se necesita interactuar directamente desde el modelo pivot

    public function property()
    {
        return $this->belongsTo(Property::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

     // Método para verificar si el cliente es el propietario
    public function isOwner()
    {
        return $this->role === 'owner';
    }
}
