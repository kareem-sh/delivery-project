<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Notification;

class FcmController extends Controller
{
    public function getNotifications(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $notifications = Notification::where('notifiable_id', $request->user_id)
            ->where('notifiable_type', 'App\Models\User')
            ->latest()
            ->paginate(10);

        return response()->json($notifications);
    }
}
