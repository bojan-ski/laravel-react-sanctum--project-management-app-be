<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Message\SendMessageRequest;
use App\Http\Resources\MessageResource;
use App\Exceptions\MessageException;
use App\Services\MessageService;
use App\Traits\ApiResponse;
use App\Models\Task;
use App\Models\Message;

class MessageController extends Controller
{
    use ApiResponse;

    public function __construct(protected readonly MessageService $messageService) {}

    /**
     * Get all messages for a task
     */
    public function index(Task $task): JsonResponse
    {
        $messages = $this->messageService->getTaskMessages($task);

        return $this->success(
            message: 'Task messages retrieved',
            data: MessageResource::collection($messages),
        );
    }

    /**
     * Send a new message
     */
    public function store(
        SendMessageRequest $request,
        Task $task
    ): JsonResponse {
        $user = $request->user();
        $messageReceiver = $task->isCreator($user) ? $task->assignee : $task->creator;

        try {
            $message = $this->messageService->sendMessage(
                task: $task,
                messageSender: $user,
                messageReceiver: $messageReceiver,
                message: $request->validated('message')
            );

            return $this->success(
                message: 'Message sent',
                data: new MessageResource($message),
                statusCode: 201
            );
        } catch (MessageException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: $e->getStatusCode()
            );
        }
    }

    /**
     * Mark all unread messages as read when user views task
     */
    public function markAsRead(
        Request $request,
        Task $task
    ): JsonResponse {
        try {
            $count = $this->messageService->markMessagesAsRead(
                task: $task,
                user: $request->user()
            );

            return $this->success(message: "{$count} message(s) marked as read");
        } catch (MessageException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: $e->getStatusCode()
            );
        }
    }

    public function destroy(
        Request $request,
        Task $task,
        Message $message
    ): JsonResponse {
        $this->messageService->validateMessage(
            userId: $request->user()->id,
            taskId: $task->id,
            message: $message,
        );

        try {
            $this->messageService->deleteMessage(
                userId: $request->user()->id,
                taskId: $task->id,
                message: $message,
            );

            return $this->success(
                message: 'Message deleted',
                data: [
                    'id' => $message->id
                ]
            );
        } catch (MessageException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: $e->getStatusCode()
            );
        }
    }
}
