<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition()
    {
        return [
            'user_id' => User::factory(), // Create a new user for the order
            'total_price' => $this->faker->randomFloat(2, 50, 500), // Random price between 50 and 500
            'order_status' => $this->faker->randomElement(['cart', 'pending', 'preparing', 'on_the_way','delivered', 'canceled']),
        ];
    }
}
