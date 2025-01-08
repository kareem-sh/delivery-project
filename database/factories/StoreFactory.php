<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

class StoreFactory extends Factory
{
    protected $model = Store::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(), // A random user associated with the store
            'name' => $this->faker->company, // Random company name as store name
            'name_ar' => $this->faker->randomElement(['نوكيا', 'سامسونغ', 'هواوي', 'سماتيل']), // Random company name as store name
            'latitude' => $this->faker->latitude, // Random latitude
            'longitude' => $this->faker->longitude, // Random longitude
            'image' => $this->faker->imageUrl(640, 480, 'business'), // Random image URL for store image
            'Logo_color' => $this->faker->hexColor, // Random color hex for store logo
        ];
    }
}
