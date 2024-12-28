<?php

namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use App\Services\Api\FcmService;
use Illuminate\Http\Request;
class FcmController extends Controller
{
    protected $fcmService;
    public function __construct(FcmService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    /**
     * Get the authenticated user's notifications.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $notifications = $this->fcmService->index();
        return response()->json($notifications);
    }

    /**
     * Send a notification to a user.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function send(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $result = $this->fcmService->send($request->user_id, $request->title, $request->message);

        if ($result > 0) {
            return response()->json(['message' => 'Notification sent successfully to ' . $result . ' device(s).'], 200);
        } else {
            return response()->json(['message' => 'No devices found for the specified user.'], 404);
        }
    }

    /**
     * Mark a notification as read.
     *
     * @param int $notificationId
     * @return \Illuminate\Http\JsonResponse
     */
    public function markAsRead($notificationId)
    {
        $result = $this->fcmService->markAsRead($notificationId);

        if ($result) {
            return response()->json(['message' => 'Notification marked as read.'], 200);
        } else {
            return response()->json(['message' => 'Notification not found.'], 404);
        }
    }
}