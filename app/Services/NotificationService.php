<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Exceptions\NotificationException;
use App\Enums\NotificationType;
use App\Enums\InvitationStatus;
use App\Models\Task;
use App\Models\Notification;
use App\Models\Project;
use App\Models\User;

class NotificationService
{
    /**
     * Create notification - project invitation
     */
    // public function projectInvitation(
    //     User $invitee,
    //     Project $project,
    //     User $inviter
    // ): Notification {
    //     $project->members()->attach($invitee->id, [
    //         'joined_at' => now(),
    //     ]);

    //     return Notification::create([
    //         'user_id' => $invitee->id,
    //         'type' => NotificationType::INVITATION,
    //         'notifiable_type' => Project::class,
    //         'notifiable_id' => $project->id,
    //         'data' => [
    //             'inviter_name' => $inviter->name,
    //             'inviter_id' => $inviter->id,
    //             'message' => "{$inviter->name} invited you to join project: {$project->title}",
    //         ],
    //         'action_taken' => InvitationStatus::ACCEPTED,
    //         'read_at' => now(),
    //     ]);
    // }
    public function projectInvitation(
        User $invitee,
        Project $project,
        User $inviter
    ): void {
        try {
            Notification::create([
                'user_id' => $invitee->id,
                'type' => NotificationType::INVITATION,
                'notifiable_type' => Project::class,
                'notifiable_id' => $project->id,
                'data' => [
                    'inviter_name' => $inviter->name,
                    'inviter_id' => $inviter->id,
                    'message' => "{$inviter->name} invited you to join project: {$project->title}",
                ]
            ]);
        } catch (\Throwable $e) {
            throw NotificationException::createNotificationFailed(
                notificationType: NotificationType::INVITATION,
                senderId: $inviter->id,
                previous: $e
            );
        }
    }

    /**
     * Create notification - member left project 
     */
    public function memberLeft(
        User $receiver,
        Project $project,
        User $sender
    ): void {
        try {
            Notification::create([
                'user_id' => $receiver->id,
                'type' => NotificationType::LEFT_THE_PROJECT,
                'notifiable_type' => Project::class,
                'notifiable_id' => $project->id,
                'data' => [
                    'sender_name' => $sender->name,
                    'sender_id' => $sender->id,
                    'message' => "{$sender->name} has left the project: {$project->title}",
                ]
            ]);
        } catch (\Throwable $e) {
            throw NotificationException::createNotificationFailed(
                notificationType: NotificationType::LEFT_THE_PROJECT,
                senderId: $sender->id,
                previous: $e
            );
        }
    }

    /**
     * Create notification - removed from project
     */
    public function removedFromProject(
        User $receiver,
        Project $project,
        User $sender
    ): void {
        try {
            Notification::create([
                'user_id' => $receiver->id,
                'type' => NotificationType::REMOVED_FROM_PROJECT,
                'notifiable_type' => Project::class,
                'notifiable_id' => $project->id,
                'data' => [
                    'sender_name' => $sender->name,
                    'sender_id' => $sender->id,
                    'message' => "{$sender->name} removed you from project: {$project->title}",
                ]
            ]);
        } catch (\Throwable $e) {
            throw NotificationException::createNotificationFailed(
                notificationType: NotificationType::REMOVED_FROM_PROJECT,
                senderId: $sender->id,
                previous: $e
            );
        }
    }

    /**
     * Create notification - project updated
     */
    public function projectUpdated(
        User $receiver,
        Project $project,
        User $sender
    ): void {
        try {
            Notification::create([
                'user_id' => $receiver->id,
                'type' => NotificationType::PROJECT_UPDATE,
                'notifiable_type' => Project::class,
                'notifiable_id' => $project->id,
                'data' => [
                    'sender_name' => $sender->name,
                    'sender_id' => $sender->id,
                    'message' => "{$sender->name} has updated the project: {$project->title}",
                ]
            ]);
        } catch (\Throwable $e) {
            throw NotificationException::createNotificationFailed(
                notificationType: NotificationType::PROJECT_UPDATE,
                senderId: $sender->id,
                previous: $e
            );
        }
    }

    /**
     * Create notification - project deleted
     */
    public function projectDeleted(
        User $receiver,
        Project $project,
        User $sender
    ): void {
        try {
            Notification::create([
                'user_id' => $receiver->id,
                'type' => NotificationType::PROJECT_DELETED,
                'notifiable_type' => Project::class,
                'notifiable_id' => $project->id,
                'data' => [
                    'sender_name' => $sender->name,
                    'sender_id' => $sender->id,
                    'message' => "{$sender->name} deleted the project: {$project->title}",
                ]
            ]);
        } catch (\Throwable $e) {
            throw NotificationException::createNotificationFailed(
                notificationType: NotificationType::PROJECT_DELETED,
                senderId: $sender->id,
                previous: $e
            );
        }
    }

    /**
     * Create notification - assigned a task
     */
    public function taskAssigned(
        User $receiver,
        Project $project,
        Task $task,
        User $sender
    ): void {
        try {
            Notification::create([
                'user_id' => $receiver->id,
                'type' => NotificationType::TASK_ASSIGNED,
                'notifiable_type' => Task::class,
                'notifiable_id' => $task->id,
                'data' => [
                    'sender_name' => $sender->name,
                    'sender_id' => $sender->id,
                    'message' => "{$sender->name} assigned you a task {$task->title}, in project: {$project->title}",
                ]
            ]);
        } catch (\Throwable $e) {
            throw NotificationException::createNotificationFailed(
                notificationType: NotificationType::TASK_ASSIGNED,
                senderId: $sender->id,
                previous: $e
            );
        }
    }

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

    /**
     * Validate notification
     */
    public function validateInvitation(Notification $notification): void
    {
        if (!$notification->isInvitation()) {
            throw NotificationException::notAnInvitation($notification->id);
        }

        if (!$notification->isPending()) {
            throw NotificationException::alreadyResponded($notification->id);
        }

        $project = $notification->notifiable;

        if (!$project) {
            throw NotificationException::projectNotFound($notification->id);
        }
    }

    /**
     * Accept project invitation
     */
    public function acceptInvitation(
        Notification $notification,
        User $user
    ): Notification {
        try {
            $project = $notification->notifiable;

            DB::transaction(function () use ($notification, $project, $user) {
                $project->members()->attach($user->id, [
                    'joined_at' => now(),
                ]);

                $notification->update([
                    'action_taken' => InvitationStatus::ACCEPTED,
                    'read_at' => now(),
                ]);
            });

            return $notification->fresh();
        } catch (\Throwable $e) {
            throw NotificationException::acceptInvitationFailed(
                notificationId: $notification->id,
                userId: $user->id,
            );
        }
    }

    /**
     * Decline project invitation
     */
    public function declineInvitation(
        Notification $notification,
        User $user
    ): Notification {
        try {
            $notification->update([
                'action_taken' => InvitationStatus::DECLINED,
                'read_at' => now(),
            ]);

            return $notification->fresh();
        } catch (\Throwable $e) {
            throw NotificationException::declineInvitationFailed(
                notificationId: $notification->id,
                userId: $user->id,
            );
        }
    }
}
