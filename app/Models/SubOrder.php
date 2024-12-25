<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'store_id',
        'sub_total',
        'order_status',
    ];

    /**
     * Get the order items for the sub-order.
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
}
