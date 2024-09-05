<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoryProduct extends Model
{
    use HasFactory;
    protected $fillable = [
        'uuid',
        'category_product_name'
    ];

    public function products()
    {
        return $this->hasMany(Product::class,'product_category_id');
    }
}
