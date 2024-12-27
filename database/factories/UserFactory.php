<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'full_name' => $this->faker->name,
            'phone_number' => $this->faker->unique()->phoneNumber,
            'verification_code' => $this->faker->numerify('#####'), // 5-digit random number
            'verification_code_expiry' => now()->addMinutes(30), // Expiry 30 mins from now
            'is_verified' => $this->faker->boolean(50), // Randomly true/false
            'lang' => $this->faker->randomElement(['en', 'ar']),
            'role' => $this->faker->randomElement(['user', 'admin', 'store_manager']),
            'latitude' => $this->faker->latitude, // Random latitude
            'longitude' => $this->faker->longitude, // Random longitude
            'theme_mode' => $this->faker->randomElement(['light', 'dark']),
            'allow_gps' => $this->faker->boolean, // Randomly true/false
            'allow_notifications' => $this->faker->boolean, // Randomly true/false
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
