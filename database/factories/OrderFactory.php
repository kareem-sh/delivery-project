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
        $itemsPrice = $this->faker->randomFloat(2, 50, 500);
        $deliveryCharge = $this->faker->numberBetween(2000, 5000);
        $subtotal = $itemsPrice + $deliveryCharge;

        return [
            'user_id' => User::factory(),
            'items_price' => $itemsPrice,
            'delivery_charge' => $deliveryCharge,
            'subtotal' => $subtotal,
            'order_status' => $this->faker->randomElement(['cart', 'pending', 'preparing', 'on the way', 'delivered', 'canceled']),
        ];
    }
}
