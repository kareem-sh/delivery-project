<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Notifications\OrderStatusChanged;

class RunEveryThirtySeconds extends Command
{
    protected $signature = 'orders:status-update';

    protected $description = 'Update order status and send notifications every 30 seconds';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        while (true) {
            // Fetch all orders
            $orders = Order::all();

            // Loop through each order to update its status
            foreach ($orders as $order) {
                if ($order->created_at <= now()->subSeconds(30) && $order->order_status == "preparing") {
                    $order->update(['order_status' => "on the way"]);
                    $order->user->notify(new OrderStatusChanged($order, 'on the way'));
                    Log::info("Order #{$order->id} status updated to 'on the way' and notification sent.");
                } elseif ($order->created_at <= now()->subSeconds(60) && $order->order_status == "on the way") {
                    $order->update(['order_status' => "delivered"]);
                    $order->user->notify(new OrderStatusChanged($order, 'delivered'));
                    Log::info("Order #{$order->id} status updated to 'delivered' and notification sent.");
                }
            }
            sleep(30);
        }
    }
}
