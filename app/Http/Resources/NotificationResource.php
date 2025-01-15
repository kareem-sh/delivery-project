<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'title' => $this->data['title'] ?? '',
            'body' => $this->data['body'] ?? '',
            'created_at' => $this->data['created_at'] ?? '',
        ];
    }
}
