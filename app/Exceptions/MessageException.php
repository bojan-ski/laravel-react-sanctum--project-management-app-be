<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Log;
use Throwable;
use Exception;

class MessageException extends Exception
{
    public const TYPE_GENERAL = 'message_feature_error';
    public const TYPE_CREATE_MESSAGE_FAILED = 'create_message_failed';
    public const TYPE_MARK_MESSAGES_AS_READ_FAILED = 'mark_messages_as_read_failed';
    public const TYPE_NOT_MESSAGE_OWNER = 'not_message_owner_failed';
    public const TYPE_CAN_NOT_FIND_TASK = 'find_task_failed';
    public const TYPE_DELETE_MESSAGE_FAILED = 'delete_message_failed';

    /**
     * Create a new exception instance
     */
    public function __construct(
        private readonly ?int $taskId = null,
        private readonly ?int $userId = null,
        private readonly ?int $messageId = null,
        string $message = 'Message error',
        int $code = 0,
        private readonly string $type = self::TYPE_GENERAL,
        private readonly int $statusCode = 500,
        private readonly string $logLevel = 'error',
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create message failed
     */
    public static function createMessageFailed(
        ?int $taskId = null,
        ?int $userId = null,
        ?Throwable $previous = null,
    ): self {
        return new self(
            taskId: $taskId,
            userId: $userId,
            message: 'Failed to create message!',
            type: self::TYPE_CREATE_MESSAGE_FAILED,
            statusCode: 500,
            logLevel: 'error',
            previous: $previous,
        );
    }

    /**
     * Mark messages as read failed
     */
    public static function markMessagesAsReadFailed(
        ?int $taskId = null,
        ?int $userId = null,
        ?Throwable $previous = null,
    ): self {
        return new self(
            taskId: $taskId,
            userId: $userId,
            message: 'Failed to mark messages as read!',
            type: self::TYPE_MARK_MESSAGES_AS_READ_FAILED,
            statusCode: 500,
            logLevel: 'error',
            previous: $previous,
        );
    }

    /**
     * Not message owner
     */
    public static function notMessageOwner(
        ?int $userId = null,
        ?int $messageId = null,
    ): self {
        return new self(
            userId: $userId,
            messageId: $messageId,
            message: 'You are not the message author!',
            type: self::TYPE_NOT_MESSAGE_OWNER,
            statusCode: 403,
            logLevel: 'warning',
        );
    }

    /**
     * Can not find task
     */
    public static function canNotFindTask(
        ?int $userId = null,
        ?int $taskId = null,
    ): self {
        return new self(
            userId: $userId,
            taskId: $taskId,
            message: 'Can not find task!',
            type: self::TYPE_CAN_NOT_FIND_TASK,
            statusCode: 403,
            logLevel: 'warning',
        );
    }

    /**
     * Delete message failed
     */
    public static function deleteMessageFailed(
        ?int $messageId = null,
        ?int $userId = null,
        ?Throwable $previous = null,
    ): self {
        return new self(
            messageId: $messageId,
            userId: $userId,
            message: 'Failed to delete message!',
            type: self::TYPE_DELETE_MESSAGE_FAILED,
            statusCode: 500,
            logLevel: 'error',
            previous: $previous,
        );
    }

    /**
     * Get HTTP status code
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Report the exception
     */
    public function report(): void
    {
        $context = array_filter([
            'type' => $this->type,
            'message' => $this->getMessage(),
            'task_id' => $this->taskId,
            'user_id' => $this->userId,
            'message_id' => $this->messageId,
            'previous' => $this->getPrevious()?->getMessage(),
        ]);

        $logLevel = $this->logLevel;

        Log::$logLevel('Message error', $context);
    }
}
