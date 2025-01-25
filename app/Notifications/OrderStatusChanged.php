<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Services\FcmService;
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
        return ['database'];
    }

    public function toArray($notifiable)
    {
        $statusMessage = $this->getStatusMessage();
        return [
            'order_id' => $this->order->id,
            'status' => $this->status,
            'title' => 'Order Status Change',
            'body' => $statusMessage,
            'created_at' => now(),
        ];
    }


    public function toFirebase($notifiable)
    {
        // Get device tokens for the user
        $tokens = Device::where('user_id', $this->order->user_id)->pluck('fcm_token')->toArray();

        if (!empty($tokens)) {
            $statusMessage = $this->getStatusMessage();

            // Use the FcmService to send notifications
            $fcmService = app(FcmService::class);

            foreach ($tokens as $token) {
                try {
                    // Call the FcmService's sendNotification method
                    $fcmService->sendNotification(
                        $token,
                        'Order Status Update',
                        $statusMessage,
                        [
                            'order_id' => $this->order->id,
                            'status' => $this->status,
                        ]
                    );

                    Log::info("Notification sent to token: {$token}");
                } catch (\Exception $e) {
                    Log::error("Failed to send notification to token {$token}: " . $e->getMessage());
                }
            }
        }
    }

    private function getStatusMessage()
    {
        return match ($this->status) {
            'preparing' => "Your order #{$this->order->id} is being prepared.",
            'on the way' => "Your order #{$this->order->id} is on the way!",
            'delivered' => "Your order #{$this->order->id} has been delivered.",
            'canceled' => "Your order #{$this->order->id} has been canceled successfully.",
            'updated' => "Your order #{$this->order->id} has been updated. Please review your order details.",
            'created' => "Your order #{$this->order->id} has been created and is pending.",
            default => "Your order #{$this->order->id} status has been updated.",
        };
    }
}
