<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Order;
use App\Models\SubOrder;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Store;
use App\Models\Category;

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

        // Create orders for users
        $users->each(function ($user) use ($stores) {
            $orders = Order::factory()->count(3)->create(['user_id' => $user->id]);

            // For each order, create sub-orders and items
            $orders->each(function ($order) use ($stores) {
                // Split each order into sub-orders for random stores
                $stores->random(2)->each(function ($store) use ($order) {
                    // Create a sub-order
                    $subOrder = SubOrder::factory()->create([
                        'order_id' => $order->id,
                        'store_id' => $store->id,
                    ]);

                    // Create order items for each sub-order
                    Product::where('store_id', $store->id)->inRandomOrder()->take(3)->get()
                        ->each(function ($product) use ($subOrder, $order) {
                            // Create order items, ensuring both order_id and sub_order_id are provided
                            OrderItem::factory()->create([
                                'order_id' => $order->id,  // Ensure order_id is assigned
                                'sub_order_id' => $subOrder->id,  // Associate with sub-order
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
