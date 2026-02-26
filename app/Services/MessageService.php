<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use App\Events\MessageSent;
use App\Events\MessageDeleted;
use App\Exceptions\MessageException;
use App\Models\Message;
use App\Models\Task;
use App\Models\User;
use Pusher\Pusher;

class MessageService
{
    public function __construct(protected readonly NotificationService $notificationService) {}

    /**
     * Get all task messages
     */
    public function getTaskMessages(Task $task): Collection
    {
        return $task->messages()
            ->with('user:id,name,avatar')
            ->oldest()
            ->get();
    }

    /**
     * Check if user is present
     */
    private function isUserPresentOnTask(
        int $taskId,
        int $userId
    ): bool {
        try {
            $pusher = new Pusher(
                config('broadcasting.connections.pusher.key'),
                config('broadcasting.connections.pusher.secret'),
                config('broadcasting.connections.pusher.app_id'),
                ['cluster' => config('broadcasting.connections.pusher.options.cluster')]
            );

            $channelName = "presence-task.{$taskId}";
            $response = $pusher->getPresenceUsers($channelName);

            $userIds = array_column($response->users ?? [], 'id');

            return in_array($userId, $userIds);
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Send a new message
     */
    public function sendMessage(
        Task $task,
        User $messageSender,
        User $messageReceiver,
        string $message
    ): ?Message {
        try {
            $message = Message::create([
                'task_id' => $task->id,
                'user_id' => $messageSender->id,
                'message' => $message,
            ]);
        } catch (\Throwable $e) {
            throw MessageException::createMessageFailed($task->id, $messageSender->id, $e);
        }

        $message->load('user');

        broadcast(new MessageSent($message))->toOthers();

        if (!$this->isUserPresentOnTask($task->id, $messageReceiver->id)) {
            $this->notificationService->newTaskMessage(
                receiver: $messageReceiver,
                task: $task,
                sender: $messageSender
            );
        }

        return $message;
    }

    /**
     * Mark messages as read for a user viewing the task
     */
    public function markMessagesAsRead(
        Task $task,
        User $user
    ): void {
        try {
            $task->messages()
                ->where('user_id', '!=', $user->id)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        } catch (\Throwable $e) {
            throw MessageException::markMessagesAsReadFailed($task->id, $user->id, $e);
        }
    }

    /**
     * Validate message
     */
    public function validateMessage(
        int $userId,
        int $taskId,
        Message $message
    ): void {
        if ($message->task_id !== $taskId) {
            throw MessageException::canNotFindTask($userId, $taskId);
        }

        if ($message->user_id !== $userId) {
            throw MessageException::notMessageOwner($message->id, $userId);
        }
    }

    /**
     * Delete a message
     */
    public function deleteMessage(
        int $userId,
        int $taskId,
        Message $message
    ): void {
        try {
            broadcast(new MessageDeleted($taskId, $message->id))->toOthers();

            $message->delete();
        } catch (\Throwable $e) {
            throw MessageException::deleteMessageFailed($message->id, $userId, $e);
        }
    }
}
