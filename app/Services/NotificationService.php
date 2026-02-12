<?php

namespace App\Services;

use Illuminate\Support\Collection;
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
    ): Notification {
        return Notification::create([
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
    }

    /**
     * Create notification - member left project 
     */
    public function memberLeft(
        User $receiver,
        Project $project,
        User $sender
    ): Notification {
        return Notification::create([
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
    }

    /**
     * Create notification - removed from project
     */
    public function removedFromProject(
        User $receiver,
        Project $project,
        User $sender
    ): Notification {
        return Notification::create([
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
    }

    /**
     * Create notification - project updated
     */
    public function projectUpdated(
        User $receiver,
        Project $project,
        User $sender
    ): Notification {
        return Notification::create([
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
    }

    /**
     * Create notification - project deleted
     */
    public function projectDeleted(
        User $receiver,
        Project $project,
        User $sender
    ): Notification {
        return Notification::create([
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
    }

    /**
     * Create notification - assigned a task
     */
    public function taskAssigned(
        User $receiver,
        Project $project,
        Task $task,
        User $sender
    ): Notification {
        return Notification::create([
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
        $project = $notification->notifiable;

        // accept invitation/add user to project
        $project->members()->attach($user->id, [
            'joined_at' => now(),
        ]);

        // accept invitation/update notification
        $notification->update([
            'action_taken' => InvitationStatus::ACCEPTED,
            'read_at' => now(),
        ]);

        return true;
    }

    /**
     * Decline project invitation
     */
    public function declineInvitation(Notification $notification): bool
    {
        $notification->update([
            'action_taken' => InvitationStatus::DECLINED,
            'read_at' => now(),
        ]);

        return true;
    }
}
