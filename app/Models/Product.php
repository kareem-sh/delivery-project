<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    protected $fillable = [
        'store_id',
        'category_id',
        'name',
        'description',
        'price',
        'stock_quantity',
        'image_url',
        'delivery_period',
        'discount_value',
        'discount_start',
        'discount_end',
    ];
    public function store()
    {
        return $this->belongsTo(Store::class);
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function order()
    {
        return $this->belongsToMany(Order::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class,'favorites','product_id','user_id');
    }
}
