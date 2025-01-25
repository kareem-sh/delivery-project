<?php

namespace App\Http\Resources\Product;

use App\Models\Category;
use App\Models\Store;
use App\Http\Resources\Store\StoreResource;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'store' => new StoreResource(Store::find($this->store_id)),
            'is_favorite' => $this->is_favorite($this->id),
            'is_cart' => $this->is_cart($this->id),
            'category' => Category::find($this->category_id)->name,
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'price' => $this->price,
            'stock_quantity' => $this->stock_quantity,
            'image_url' =>  $this->image_url ? asset('storage/' . $this->image_url) : null,
            'delivery_period' => $this->delivery_period,
            'discount_value' => $this->discount_value,
            'discount_start' => $this->discount_start,
            'discount_end' => $this->discount_end,

        ];
    }
}
