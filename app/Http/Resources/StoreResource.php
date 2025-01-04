<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "name" => $this->name,
            "latitude" => $this->latitude,
            "longitude" => $this->longitude,
            "logo_color" => $this->logo_color,
            "image" => $this->image,
            "products count" => $this->products->count()
        ];
    }
}
