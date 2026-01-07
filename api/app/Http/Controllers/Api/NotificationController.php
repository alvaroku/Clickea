<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Get all notifications for authenticated user
     */
    public function index(Request $request)
    {
        $query = Notification::where('user_id', $request->user()->id);

        // Filter by search term
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('message', 'like', "%{$search}%");
            });
        }

        // Filter by read status
        if ($request->filled('is_read')) {
            $query->where('is_read', $request->boolean('is_read'));
        }

        $notifications = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'data' => $notifications
        ]);
    }

    /**
     * Get unread count
     */
    public function unreadCount(Request $request)
    {
        $count = Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'count' => $count
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Request $request, Notification $notification)
    {
        // Check ownership
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $notification->update(['is_read' => true]);

        return response()->json([
            'message' => 'Notificación marcada como leída.',
            'data' => $notification
        ]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead(Request $request)
    {
        $updated = Notification::where('user_id', $request->user()->id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'message' => "Se marcaron {$updated} notificaciones como leídas.",
            'count' => $updated
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy(Request $request, Notification $notification)
    {
        // Check ownership
        if ($notification->user_id !== $request->user()->id) {
            return response()->json(['message' => 'No autorizado.'], 403);
        }

        $notification->delete();

        return response()->json([
            'message' => 'Notificación eliminada.'
        ]);
    }

    /**
     * Delete all read notifications
     */
    public function deleteAllRead(Request $request)
    {
        $deleted = Notification::where('user_id', $request->user()->id)
            ->where('is_read', true)
            ->delete();

        return response()->json([
            'message' => "Se eliminaron {$deleted} notificaciones leídas.",
            'count' => $deleted
        ]);
    }
}
