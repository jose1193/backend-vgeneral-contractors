<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class Product extends Model
{
    use HasFactory;
     use SoftDeletes;
     protected $fillable = [
        'uuid',
        'product_category_id',
        'product_name',
        'product_description',
        'price',
        'unit',
        'order_position',
    ];

    public function categoryProduct()
    {
        return $this->belongsTo(CategoryProduct::class,'product_category_id');
    }
}
