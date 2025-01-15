<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\Category;
use App\Models\Device;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run()
    {
        // Create categories
        $categories = Category::factory()->count(5)->create();

        // Create stores
        $stores = Store::factory()->count(5)->create();

        // Create products for each store and category
        $stores->each(function ($store) use ($categories) {
            Product::factory()->count(10)->create([
                'store_id' => $store->id,
                'category_id' => $categories->random()->id, // Assign a random category to each product
            ]);
        });

        // Create users
        $users = User::factory()->count(10)->create();

        // Create devices and orders for each user
        $users->each(function ($user) use ($stores) {
            // Create 1-2 devices for each user
            Device::factory()->count(rand(1, 2))->create([
                'user_id' => $user->id,
            ]);

            // Create 3 orders for each user
            $orders = Order::factory()->count(3)->create(['user_id' => $user->id]);

            // For each order, create order items
            $orders->each(function ($order) use ($stores) {
                // Select random stores for the order
                $stores->random(2)->each(function ($store) use ($order) {
                    // Get random products from the selected store
                    Product::where('store_id', $store->id)
                        ->inRandomOrder()
                        ->take(3)
                        ->get()
                        ->each(function ($product) use ($order) {
                            // Create order items for the order
                            OrderItem::factory()->create([
                                'order_id' => $order->id,
                                'product_id' => $product->id,
                                'price' => $product->price,
                                'quantity' => rand(1, 5),
                            ]);
                        });
                });
            });
        });
    }
}
