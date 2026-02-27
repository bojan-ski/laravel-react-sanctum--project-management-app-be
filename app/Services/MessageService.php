<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Collection;
use App\Exceptions\MessageException;
use App\Models\Message;
use App\Models\Task;
use App\Models\User;

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
     * Send a new message
     */
    public function sendMessage(
        Task $task,
        User $messageSender,
        string $message
    ): ?Message {
        try {
            $newMessage = Message::create([
                'task_id' => $task->id,
                'user_id' => $messageSender->id,
                'message' => $message,
            ]);

            return $newMessage->load('user');
        } catch (\Throwable $e) {
            throw MessageException::createMessageFailed($task->id, $messageSender->id, $e);
        }
    }

    /**
     * Mark messages as read for a user viewing the task
     */
    public function markMessagesAsRead(
        Task $task,
        User $user
    ): int {
        try {
            return $task->messages()
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
    public function deleteMessage(Message $message): void
    {
        try {
            $message->delete();
        } catch (\Throwable $e) {
            throw MessageException::deleteMessageFailed($message->id, $message->user_id, $e);
        }
    }
}
