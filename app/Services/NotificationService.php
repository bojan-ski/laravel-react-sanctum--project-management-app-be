<?php

namespace App\Services;

use Illuminate\Support\Collection;
use App\Models\Notification;
use App\Models\Project;
use App\Models\User;
use App\Enums\NotificationType;
use App\Enums\InvitationStatus;

class NotificationService
{
    /**
     * Create project invitation notification
     */
    public function createInvitation(
        User $invitee,
        Project $project,
        User $inviter
    ): Notification {
        return Notification::create([
            'user_id' => $invitee->id,
            'type' => NotificationType::INVITATION->value,
            'notifiable_type' => Project::class,
            'notifiable_id' => $project->id,
            'data' => [
                'inviter_name' => $inviter->name,
                'inviter_id' => $inviter->id,
                'message' => "{$inviter->name} invited you to join {$project->title}",
            ]
        ]);
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications(
        User $user,
        ?bool $unreadOnly = false
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
    public function markAsRead(Notification $notification): bool
    {
        $notification->markAsRead();

        return true;
    }

    /**
     * Mark all user notifications as read
     */
    public function markAllAsRead(User $user): int
    {
        return $user->notifications()->unread()->update(['read_at' => now()]);
    }

    /**
     * Accept project invitation
     */
    public function acceptInvitation(
        Notification $notification,
        User $user
    ): bool {
        if (!$notification->isInvitation() || !$notification->isPending()) {
            return false;
        }

        $project = $notification->notifiable;

        // accept invitation/add user to project
        $project->members()->attach($user->id, [
            'joined_at' => now(),
        ]);

        // accept invitation/update notification
        $notification->update([
            'action_taken' => InvitationStatus::ACCEPTED->value,
            'read_at' => now(),
        ]);

        return true;
    }

    /**
     * Decline project invitation
     */
    public function declineInvitation(Notification $notification): bool
    {
        if (!$notification->isInvitation() || !$notification->isPending()) {
            return false;
        }

        // decline invitation/update notification
        $notification->update([
            'action_taken' => InvitationStatus::DECLINED->value,
            'read_at' => now(),
        ]);

        return true;
    }
}
