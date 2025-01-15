<?php

namespace App\Notifications;

use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\Device;
use Illuminate\Contracts\Queue\ShouldQueue;

class CustomMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $customMessage;
    public $title;

    public function __construct($customMessage, $title = 'Custom Notification')
    {
        $this->customMessage = $customMessage;
        $this->title = $title;
    }

    public function via($notifiable)
    {
        return ['database', 'firebase'];
    }

    public function toArray($notifiable)
    {
        return [
            'message' => $this->customMessage,
        ];
    }

    public function toFirebase($notifiable)
    {
        $devices = Device::where('user_id', $notifiable->id)->get();

        $firebaseMessaging = app('firebase.messaging');
        $tokens = $devices->pluck('fcm_token')->toArray();

        if (!empty($tokens)) {
            $message = CloudMessage::withTarget('tokens', $tokens)
                ->withNotification([
                    'title' => $this->title,
                    'body' => $this->customMessage,
                ]);

            $firebaseMessaging->send($message);
        }
    }
}
