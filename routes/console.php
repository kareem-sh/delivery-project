<?php

use App\Models\Order;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::call(function () {
    $orders=Order::all();
    foreach($orders as $order)
    {
        if($order->order_date<=now()->subMinutes(1)&&$order->order_status=="preparing")
        {
           $order->update(['order_status'=>"on the way"]);
        }
        else if($order->order_date<=now()->subMinutes(2)&&$order->order_status=="on the way")
        {
        $order->update(['order_status'=>"delivered"]);
        }
    }
})->everyMinute();
