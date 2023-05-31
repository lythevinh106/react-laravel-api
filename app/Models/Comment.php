<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'comment_text',
        'product_id',
        'user_id',
        'parent_comment_id'




    ];


    public function user()
    {

        return $this->belongsTo(User::class, "user_id");
    }

    public function product()
    {

        return $this->belongsTo(Product::class, "product_id");
    }


    // public function product_images()
    // {

    //     return $this->hasMany(Product_images::class, "product_id", "id");
    // }




}
