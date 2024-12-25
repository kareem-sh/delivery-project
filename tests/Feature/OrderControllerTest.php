<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use App\Models\Order;
use App\Models\SubOrder;
use App\Models\OrderItem;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test the creation of an order.
     */
    public function test_user_can_create_order()
    {
        // Create a user
        $user = User::factory()->create();

        // Create some products
        $product1 = Product::factory()->create(['price' => 10.00]);
        $product2 = Product::factory()->create(['price' => 15.00]);

        // Order items to send in the request
        $orderItems = [
            [
                'product_id' => $product1->id,
                'quantity' => 2,
                'price' => $product1->price,
            ],
            [
                'product_id' => $product2->id,
                'quantity' => 1,
                'price' => $product2->price,
            ],
        ];

        // Make a POST request to the store method
        $response = $this->postJson(route('orders.store'), [
            'user_id' => $user->id,
            'order_items' => $orderItems,
        ]);

        // Assertions
        $response->assertStatus(201);

        // Verify the main order is created
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'order_status' => 'pending',
        ]);

        // Verify the sub-orders are created
        $this->assertDatabaseCount('sub_orders', 2);

        // Verify the order items are created
        $this->assertDatabaseCount('order_items', 2);

        // Check the response structure
        $response->assertJsonStructure([
            'order' => [
                'id',
                'user_id',
                'total_price',
                'order_status',
                'sub_orders' => [
                    '*' => [
                        'id',
                        'store_id',
                        'sub_total',
                        'order_status',
                        'order_items' => [
                            '*' => [
                                'id',
                                'order_id',
                                'product_id',
                                'sub_order_id',
                                'quantity',
                                'price',
                                'product' => [
                                    'id',
                                    'name',
                                    'description',
                                    'price',
                                    'store_id',
                                ],
                            ],
                        ],
                    ],
                ],
                'order_items',
                'created_at',
                'updated_at',
            ],
        ]);
    }
}
