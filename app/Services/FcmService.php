<?php

namespace App\Services\Api;

use App\Models\Notification as NotificationModel;
use App\Models\Device;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;

class FcmService
{
    public function index()
    {
        return auth()->user()->notifications;
    }

    public function send($userId, $title, $message)
    {
        // Path to the service account key JSON file
        $serviceAccountPath = storage_path('app/firebase_credentials.json');

        // Initialize the Firebase Factory with the service account
        $factory = (new Factory)->withServiceAccount($serviceAccountPath);

        // Create the Messaging instance
        $messaging = $factory->createMessaging();

        // Prepare the notification array
        $notification = [
            'title' => $title,
            'body' => $message,
        ];

        // Retrieve the user's devices
        $devices = Device::where('user_id', $userId)->get();

        if ($devices->isEmpty()) {
            Log::warning("No devices found for user ID: {$userId}");
            return 0; // No devices to send notifications to
        }

        $sentCount = 0;

        foreach ($devices as $device) {
            // Create the CloudMessage instance
            $cloudMessage = CloudMessage::withTarget('token', $device->fcm_token)
                ->withNotification($notification);

            try {
                // Send the notification
                $messaging->send($cloudMessage);

                // Save the notification to the database
                NotificationModel::query()->create([
                    'user_id' => $userId,
                    'title' => $title,
                    'message' => $message,
                    'is_read' => false,
                ]);

                $sentCount++;
            } catch (\Kreait\Firebase\Exception\MessagingException $e) {
                Log::error("MessagingException: " . $e->getMessage());
            } catch (\Kreait\Firebase\Exception\FirebaseException $e) {
                Log::error("FirebaseException: " . $e->getMessage());
            }
        }

        return $sentCount; // Return the number of successfully sent notifications
    }

    public function markAsRead($notificationId): bool
    {
        $notification = auth()->user()->notifications()->findOrFail($notificationId);

        if (isset($notification)) {
            $notification->is_read = true;
            $notification->save();
            return true;
        } else {
            return false;
        }
    }
}