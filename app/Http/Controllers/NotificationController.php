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

    public function markAsSeen(Request $request, Notification $notification)
    {
        $user = $request->user();

        if ($notification->receiver_user_id !== $user->id) {
            return response()->json([
                'message' => 'Táto notifikácia nepatrí prihlásenému používateľovi.',
            ], 403);
        }

        if (is_null($notification->seen_at)) {
            $notification->seen_at = now();
            $notification->save();
        }

        return response()->json($notification);
    }
}
