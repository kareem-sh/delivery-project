<?php

use App\Models\Order;
use AppServices\FcmService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Notifications\OrderStatusChanged;
use Illuminate\Support\Facades\Log;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

use App\Console\Commands\RunEveryThirtySeconds;

Artisan::command('orders:status-update', function () {
    $this->call(RunEveryThirtySeconds::class);
});

// Schedule::call(function () {
//     $orders = Order::all();

//     foreach ($orders as $order) {
//         // Check if the order is "preparing" and update status to "on the way" after 30 seconds
//         if ($order->created_at <= now()->subSeconds(30) && $order->order_status == "preparing") {
//             $order->update(['order_status' => "on the way"]);
//             // Send notification using the OrderStatusChanged notification
//             $order->user->notify(new OrderStatusChanged($order, 'on the way'));
//             Log::info("Order #{$order->id} status updated to 'on the way' and notification sent.");
//         }

//         // Check if the order is "on the way" and update status to "delivered" after 60 seconds
//         elseif ($order->created_at <= now()->subSeconds(60) && $order->order_status == "on the way") {
//             $order->update(['order_status' => "delivered"]);
//             // Send notification using the OrderStatusChanged notification
//             $order->user->notify(new OrderStatusChanged($order, 'delivered'));
//             Log::info("Order #{$order->id} status updated to 'delivered' and notification sent.");
//         }
//     }
// })->everyMinute(); // Executes every minute
