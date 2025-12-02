<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $notifications = Notification::query()
            ->where('receiver_user_id', $user->id)
            ->orderByDesc('sent_at')
            ->get();

        return response()->json($notifications);
    }
}
