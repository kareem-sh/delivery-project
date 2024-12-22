<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\FacadesDB;
use Illuminate\Support\Facades\Http;
use DB;

class FcmController extends Controller
{
    public function sendOrderStatusNotification($userId, $title, $body, $data = [])
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $notificationData = [
            'title' => $title,
            'body' => $body,
            'data' => $data,
        ];

        DB::table('notifications')->insert([
            'user_id' => $user->id,
            'title' => $notificationData['title'],
            'body' => $notificationData['body'],
            'data' => json_encode($notificationData['data']),
            'created_at' => now(),
        ]);

        $this->sendFCMNotification($user->fcm_token, $notificationData);

        return response()->json(['message' => 'Notification sent successfully']);
    }

    private function sendFCMNotification($token, $notificationData)
    {
        $serverKey = env('FCM_SERVER_KEY');
        $url = 'https://fcm.googleapis.com/fcm/send';

        $headers = [
            'Authorization: key=' . $serverKey,
            'Content-Type: application/json',
        ];

        $data = [
            'to' => $token,
            'notification' => [
                'title' => $notificationData['title'],
                'body' => $notificationData['body'],
                'sound' => 'default',
            ],
            'data' => $notificationData['data'],
        ];

        Http::withHeaders($headers)->post($url, $data);
    }
}
