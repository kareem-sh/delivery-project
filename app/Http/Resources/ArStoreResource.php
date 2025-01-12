<?php

namespace App\Http\Resources;

use App\Traits\EnglishToArabic;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ArStoreResource extends JsonResource
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
            "id" => $this->id,
            "name" => $this->name_ar,
            "latitude" => $this->latitude,
            "longitude" => $this->longitude,
            "logo_color" => $this->logo_color,
            "image" => $this->image ? asset('storage/' . $this->image) : null,
            "products count" => $this->convertToArabicNumbers($this->products->count())
        ];
    }
}
