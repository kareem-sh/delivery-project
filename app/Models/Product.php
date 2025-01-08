<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'store_id',
        'category_id',
        'name',
        'name_ar',
        'description',
        'description_ar',
        'price',
        'stock_quantity',
        'image_url',
        'discount_value',
        'discount_start',
        'discount_end',
        'delivery_period',
    ];

    /**
     * Get the effective price considering the discount, if applicable.
     *
     * @return float
     */
    public function getEffectivePriceAttribute()
    {
        $currentDate = now();

        $effectivePrice = $this->price; // Default to the original price

        // Check if a discount is active
        if (
            $this->discount_start &&
            $this->discount_end &&
            $currentDate->between($this->discount_start, $this->discount_end)
        ) {

            // Calculate percentage discount
            $discount = $this->price * ($this->discount_value / 100);
            $effectivePrice = $this->price - $discount;
        }

        // Ensure the price is never less than zero
        $effectivePrice = max($effectivePrice, 0);

        // Format the price to 2 decimal places and return
        return (float)sprintf('%.2f', $effectivePrice);
    }


    public function hasSufficientStock(int $quantity)
    {
        return $this->stock_quantity >= $quantity;
    }


    /**
     * Relationship: A product belongs to a store
     */
    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    /**
     * Relationship: A product belongs to a category
     */
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function is_favorite($id)
    {
        $user = Auth::user();
        $is_favorite = Favorite::where('user_id', $user->id)->where('product_id', $id)->get();
        if (!$is_favorite->isEmpty()) {
            return true;
        }
        return false;
    }
    public function is_cart($id)
    {
        $user = Auth::user();
        $cartOrder = Order::where('user_id', $user->id)
            ->where('order_status', 'cart')
            ->with('orderItems')->get();
        $order_items = $cartOrder->flatMap->orderItems;
        foreach ($order_items as $item) {
            if ($item['product_id'] == $this->id) {
                return true;
            }
        }
        return false;
    }
}
