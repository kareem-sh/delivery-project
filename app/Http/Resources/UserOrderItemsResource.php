<?php

namespace App\Http\Resources;

use GPBMetadata\Google\Api\Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserOrderItemsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'product_id' => $this->product->id,
            'name' => auth()->user()->lang == 'en' ? $this->product->name : $this->product->name_ar,
            'price' => $this->price,
            'quantity' => $this->quantity,
        ];
    }
}
