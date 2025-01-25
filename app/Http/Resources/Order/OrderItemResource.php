<?php

namespace App\Http\Resources\Order;

use App\Http\Resources\Product\ProductResource;
use App\Http\Resources\Product\ArProductResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class OrderItemResource extends JsonResource
{
    public function toArray($request)
    {
        return [

            'product_details' => Auth::user()->lang == 'en' ? new ProductResource($this->product) : new ArProductResource($this->product),
            'quantity' => $this->quantity,
        ];
    }
}
