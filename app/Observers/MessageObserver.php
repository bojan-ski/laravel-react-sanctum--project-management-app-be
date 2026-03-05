<?php

namespace App\Observers;

use Pusher\Pusher;
use App\Events\MessageDeleted;
use App\Events\MessageSent;
use App\Services\NotificationCreationService;
use App\Models\Message;

class MessageObserver
{
    public function __construct(protected readonly NotificationCreationService $notificationCreationService) {}

    /**
     * Check if user is present on task channel.
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

            $userIds = array_map(
                fn($user) => (int) $user->id,
                $response->users ?? []
            );

            $isPresent = in_array($userId, $userIds);

            return $isPresent;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Notify receiver if they're not on the task page.
     */
    private function notifyReceiverIfNotPresent(Message $message): void
    {
        $task = $message->task;
        $sender = $message->user;

        $receiver = $task->isCreator($sender) ? $task->assignee : $task->creator;

        if (!$receiver) return;

        if (!$this->isUserPresentOnTask($task->id, $receiver->id)) {
            $this->notificationCreationService->newTaskMessage(
                receiver: $receiver,
                task: $task,
                sender: $sender
            );
        }
    }

    /**
     * Handle the Message "created" event.
     */
    public function created(Message $message): void
    {
        $message->load('user');

        broadcast(new MessageSent($message))->toOthers();

        $this->notifyReceiverIfNotPresent($message);
    }

    /**
     * Handle the Message "deleted" event.
     */
    public function deleted(Message $message): void
    {
        broadcast(new MessageDeleted(
            taskId: $message->task_id,
            messageId: $message->id
        ))->toOthers();
    }
}
