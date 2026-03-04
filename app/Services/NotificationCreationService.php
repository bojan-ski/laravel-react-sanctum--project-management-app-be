<?php

namespace App\Services;

use App\Exceptions\NotificationException;
use App\Enums\NotificationType;
use App\Models\Task;
use App\Models\Notification;
use App\Models\Project;
use App\Models\User;

class NotificationCreationService
{
    /**
     * Create notification - project invitation
     */
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
                    'sender_name' => $inviter->name,
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
     * Create notification - task status changed
     */
    public function taskStatusChanged(
        User $receiver,
        Task $task,
        User $sender
    ): void {
        try {
            Notification::create([
                'user_id' => $receiver->id,
                'type' => NotificationType::TASK_STATUS_CHANGED,
                'notifiable_type' => Task::class,
                'notifiable_id' => $task->id,
                'data' => [
                    'sender_name' => $sender->name,
                    'message' => "{$sender->name} changed the status of the task:{$task->title}",
                ]
            ]);
        } catch (\Throwable $e) {
            throw NotificationException::createNotificationFailed(
                notificationType: NotificationType::TASK_STATUS_CHANGED,
                senderId: $sender->id,
                previous: $e
            );
        }
    }

    /**
     * Create notification - task priority changed
     */
    public function taskPriorityChanged(
        User $receiver,
        Task $task,
        User $sender
    ): void {
        try {
            Notification::create([
                'user_id' => $receiver->id,
                'type' => NotificationType::TASK_PRIORITY_CHANGED,
                'notifiable_type' => Task::class,
                'notifiable_id' => $task->id,
                'data' => [
                    'sender_name' => $sender->name,
                    'message' => "{$sender->name} changed the priority of the task:{$task->title}",
                ]
            ]);
        } catch (\Throwable $e) {
            throw NotificationException::createNotificationFailed(
                notificationType: NotificationType::TASK_PRIORITY_CHANGED,
                senderId: $sender->id,
                previous: $e
            );
        }
    }

    /**
     * Create notification - task priority changed
     */
    public function taskDeleted(
        User $receiver,
        Task $task,
        User $sender
    ): void {
        try {
            Notification::create([
                'user_id' => $receiver->id,
                'type' => NotificationType::TASK_DELETED,
                'notifiable_type' => Task::class,
                'notifiable_id' => $task->id,
                'data' => [
                    'sender_name' => $sender->name,
                    'message' => "{$sender->name} deleted the task:{$task->title}",
                ]
            ]);
        } catch (\Throwable $e) {
            throw NotificationException::createNotificationFailed(
                notificationType: NotificationType::TASK_DELETED,
                senderId: $sender->id,
                previous: $e
            );
        }
    }

    /**
     * Create notification - new task message
     */
    public function newTaskMessage(
        User $receiver,
        Task $task,
        User $sender
    ): Notification {
        return Notification::create([
            'user_id' => $receiver->id,
            'type' => NotificationType::TASK_MESSAGE,
            'notifiable_type' => Task::class,
            'notifiable_id' => $task->id,
            'data' => [
                'sender_name' => $sender->name,
                'message' => "{$sender->name} sent a message in task: {$task->title}",
            ]
        ]);
    }
}
