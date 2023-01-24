<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;
    protected $fillable = [
        'id',
        'status',
        'user_id',
        "customer_id",
        "order_token",
        "order_date"


    ];

    public function order_items()
    {

        return $this->hasMany(Order_items::class, "order_id", "id");
    }


    public function products()
    {

        return $this->belongsToMany(Product::class, "order_items");
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
