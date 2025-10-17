<?php


namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Fetch all notifications for modal (AJAX)
     */


    public function navbar()
    {
        $user = auth()->user();

        if ($user->role === 'Supplier') {
            // Supplier only sees their own notifications
            $notificationsQuery = Notification::where('notifiable_id', $user->id)
                ->where('notifiable_type', get_class($user));
        } else {
            // Admins and others see all
            $notificationsQuery = Notification::query();
        }

        $unreadCount = $notificationsQuery->whereNull('read_at')->count();
        $unreadNotifications = $notificationsQuery->whereNull('read_at')->latest()->take(5)->get();

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $unreadNotifications,
        ]);
    }

    public function fetchAll()
    {
        $user = auth()->user();

        // Base query
        $query = Notification::query()->latest();

        // Role-based filtering
        if ($user->role == 'Supplier') {
            // Only supplier’s notifications
            $query->where('notifiable_id', $user->id)
                  ->where('notifiable_type', get_class($user));
        }

        // Fetch latest 50
        $notifications = $query->take(50)->get()->map(function ($notification) {
            return [
                'id' => $notification->id,
                'title' => $notification->data['title'] ?? 'Notification',
                'message' => $notification->data['message'] ?? '',
                'order_id' => $notification->data['order_id'] ?? null,
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at->diffForHumans(),
            ];
        });
        // Unread count (filtered accordingly)
        $unreadCount = ($user->role == 'Supplier')
            ? $user->unreadNotifications->count()
            : Notification::whereNull('read_at')->count();

        return response()->json([
            'unread_count' => $unreadCount,
            'notifications' => $notifications,
        ]);
    }

    /**
     * Mark single notification as read
     */
     public function markAsRead($id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);

        // Mark as read if unread
        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        // Redirect to URL if exists, else back
        $redirectUrl = $notification->data['url'] ?? url()->previous();

        return redirect($redirectUrl);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();
        return response()->json(['status' => 'success']);
    }
}



