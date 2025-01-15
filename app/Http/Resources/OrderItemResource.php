<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [

            'product_details' => new ProductResource($this->product),
            'quantity' => $this->quantity,
        ];
    }
}
