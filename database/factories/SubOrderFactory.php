<?php

namespace Database\Factories;

use App\Models\SubOrder;
use App\Models\Order;
use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class SubOrderFactory extends Factory
{
    protected $model = SubOrder::class;

    public function definition()
    {
        return [
            'order_id' => Order::factory(),
            'store_id' => Store::factory(),
            'sub_total' => $this->faker->randomFloat(2, 10, 500),
            'order_status' => $this->faker->randomElement(['cart', 'pending', 'preparing', 'on the way','delivered', 'canceled']),
        ];
    }
}
