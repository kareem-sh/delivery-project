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

    // Add the sendNotification function
    private function sendNotification($user, $order, $status)
    {
        $notification = new OrderStatusChanged($order, $status);
        $user->notify($notification);
        $notification->toFirebase($user);
    }

    public function handle()
    {
        while (true) {
            // Fetch all orders
            $orders = Order::all();

            // Loop through each order to update its status
            foreach ($orders as $order) {
                // Check if the order is still pending and update to preparing
                if ($order->created_at <= now()->subSeconds(30) && $order->order_status == "pending") {
                    $order->update(['order_status' => "preparing"]);
                    $this->sendNotification($order->user, $order, 'preparing');
                }

                // Update from preparing to on the way
                else if ($order->created_at <= now()->subSeconds(30) && $order->order_status == "preparing") {
                    $order->update(['order_status' => "on the way"]);
                    $this->sendNotification($order->user, $order, 'on the way');
                }

                // Update from on the way to delivered
                elseif ($order->created_at <= now()->subSeconds(60) && $order->order_status == "on the way") {
                    $order->update(['order_status' => "delivered"]);
                    $this->sendNotification($order->user, $order, 'delivered');
                }
            }

            sleep(30);
        }
    }
}
