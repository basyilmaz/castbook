<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Kullanıcının bildirimlerini getir (AJAX)
     */
    public function index(Request $request): JsonResponse
    {
        $userId = auth()->id();
        
        $notifications = Notification::query()
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->orWhereNull('user_id'); // Global bildirimler
            })
            ->recent(15)
            ->get();

        $unreadCount = Notification::query()
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->orWhereNull('user_id');
            })
            ->unread()
            ->count();

        return response()->json([
            'notifications' => $notifications->map(fn ($n) => [
                'id' => $n->id,
                'type' => $n->type,
                'title' => $n->title,
                'message' => $n->message,
                'icon_class' => $n->icon_class,
                'link' => $n->link,
                'read' => $n->read_at !== null,
                'time_ago' => $n->created_at->diffForHumans(),
                'created_at' => $n->created_at->toIso8601String(),
            ]),
            'unread_count' => $unreadCount,
        ]);
    }

    /**
     * Bildirimi okundu olarak işaretle
     */
    public function markAsRead(Notification $notification): JsonResponse
    {
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Tüm bildirimleri okundu olarak işaretle
     */
    public function markAllAsRead(): JsonResponse
    {
        $userId = auth()->id();

        Notification::query()
            ->where(function ($query) use ($userId) {
                $query->where('user_id', $userId)
                      ->orWhereNull('user_id');
            })
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }
}
