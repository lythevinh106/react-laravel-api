<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product_images extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'name',
        'product_id',


    ];


    public function product()
    {
        return $this->belongsTo(Product::class, "product_id", "id");
    }
}
