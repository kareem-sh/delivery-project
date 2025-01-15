<?php

namespace App\Notifications;

use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\User;
use App\Models\Device;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;


class OrderStatusChanged extends Notification implements ShouldQueue
{
    use Queueable;

    public $order;
    public $status;

    public function __construct($order, $status)
    {
        $this->order = $order;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return ['database', 'firebase'];
    }

    public function toArray($notifiable)
    {
        return [
            'order_id' => $this->order->id,
            'status' => $this->status,
            'message' => 'Your order status has been updated.',
        ];
    }

    public function toFirebase($notifiable)
    {
        $firebaseMessaging = app('firebase.messaging');
        $tokens = Device::where('user_id', $this->order->user_id)->pluck('fcm_token')->toArray();

        if (!empty($tokens)) {
            $statusMessage = match ($this->status) {
                'preparing' => "Your order #{$this->order->id} is being prepared.",
                'on the way' => "Your order #{$this->order->id} is on the way!",
                'delivered' => "Your order #{$this->order->id} has been delivered.",
                'canceled' => "Your order #{$this->order->id} has been canceled. We’re sorry for the inconvenience.",
                'updated' => "Your order #{$this->order->id} has been updated. Please review your order details.",
                default => "Your order #{$this->order->id} status has been updated.",
            };

            $message = CloudMessage::withTarget('tokens', $tokens)
                ->withNotification([
                    'title' => 'Order Status Update',
                    'body' => $statusMessage,
                ]);

            try {
                $firebaseMessaging->send($message);
            } catch (\Exception $e) {
                Log::error("Firebase notification failed: " . $e->getMessage());
            }
        }
    }
}
