<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'full_name' => $this->full_name,
            'phone_number' => $this->phone_number,
            'lang' => $this->lang,
            'image' => $this->image ? asset('storage/' . $this->image) : null,
            'role' => $this->role,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'theme_mode' => $this->theme_mode,
            'allow_gps' => $this->allow_gps,
            'allow_notifications' => $this->allow_notifications,
            'unreadNotificationsCount' => $this->unreadNotificationsCount()
        ];
    }
}
