<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'sub_order_id', 
        'quantity',
        'price',
    ];

    /**
     * Get the sub-order that owns the order item.
     */
    public function subOrder()
    {
        return $this->belongsTo(SubOrder::class);
    }

    public function order(){
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the product that owns the order item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
