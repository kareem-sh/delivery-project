<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserOrdersResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'items_price' => $this->items_price,
            'delivery_charge' => $this->delivery_charge,
            'subtotal' => $this->subtotal,
            'order_status' => $this->order_status,
            'order_items' => UserOrderItemsResource::collection($this->whenLoaded('orderItems')),
        ];
    }
}
