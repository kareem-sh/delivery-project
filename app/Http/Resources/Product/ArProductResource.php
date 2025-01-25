<?php

namespace App\Http\Resources\Product;

use App\Models\Category;
use App\Models\Store;
use App\Http\Resources\Store\ArStoreResource;
use App\Traits\EnglishToArabic;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArProductResource extends JsonResource
{
    use EnglishToArabic;
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'store' => new ArStoreResource(Store::find($this->store_id)),
            'is_favorite' => $this->is_favorite($this->id),
            'is_cart' => $this->is_cart($this->id),
            'category' => Category::find($this->category_id)->name_ar,
            'id' => $this->id,
            'name' => $this->name_ar,
            'description' => $this->description_ar,
            'price' => $this->price,
            'stock_quantity' => $this->stock_quantity,
            'image_url' =>  $this->image_url ? asset('storage/' . $this->image_url) : null,
            'delivery_period' => $this->convertToArabicNumbers($this->delivery_period),
            'discount_value' => $this->discount_value,
            'discount_start' => $this->convertToArabicNumbers($this->discount_start),
            'discount_end' => $this->convertToArabicNumbers($this->discount_end),

        ];
    }
}
