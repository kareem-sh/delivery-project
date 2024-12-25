<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable=[
        'user_id',
        'product_id',
        'quantity',
        'price',
        'order_status',
        'order_date'
    ];
    public function user()
    {
        $this->belongsTo(User::class);
    }
    public function product()
    {
        $this->hasOne(Product::class);
    }
}
