<?php

use App\Models\Order;
use AppServices\FcmService;
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
            $service=new FcmService();
           $service->sendNotification($order->user->fcm_token,"Hello ".$order->user->full_name,"the order with product ".$order->product->name." on the way",[$order->id]);
        }
        else if($order->order_date<=now()->subMinutes(2)&&$order->order_status=="on the way")
        {
            $order->update(['order_status'=>"delivered"]);
             $service=new FcmService();
            $service->sendNotification($order->user->fcm_token,"Hello ".$order->user->full_name,"the order with product ".$order->product->name." delivered",[$order->id]);
        }
    }
})->everyMinute();
