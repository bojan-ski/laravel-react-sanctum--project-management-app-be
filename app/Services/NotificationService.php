<?php

namespace App\Services;

use Illuminate\Support\Collection;

use App\Exceptions\NotificationException;
use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Get user notifications
     */
    public function getUserNotifications(
        User $user,
        bool $unreadOnly
    ): Collection {
        $query = $user->notifications()->with('notifiable')->latest();

        if ($unreadOnly) {
            $query->unread();
        }

        return $query->get();
    }

    /**
     * Get unread count
     */
    public function getUnreadCount(User $user): int
    {
        return $user->notifications()->unread()->count();
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(Notification $notification): void
    {
        try {
            $notification->markAsRead();
        } catch (\Throwable $e) {
            throw NotificationException::markAsReadFailed(
                notificationId: $notification->id
            );
        }
    }

    /**
     * Mark all user notifications as read
     */
    public function markAllAsRead(User $user): int
    {
        try {
            return $user->notifications()->unread()->update(['read_at' => now()]);
        } catch (\Throwable $e) {
            throw NotificationException::markAllAsReadFailed();
        }
    }
}
