<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'name',
        'title',
        'description', 'image', 'origin_price', 'sale_price', 'active', "category_id"

    ];


    public function category()
    {

        return $this->belongsTo(Category::class, "category_id", "id");
    }


    public function product_images()
    {

        return $this->hasMany(Product_images::class, "product_id", "id");
    }
}
