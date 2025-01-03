<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'product_id' => $this->product_id,
            'product' => $this->product->name,
            'quantity' => $this->quantity,
            'price' => $this->product->getEffectivePriceAttribute()


        ];
    }
}
