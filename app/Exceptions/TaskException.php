<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Log;
use Throwable;
use Exception;

class TaskException extends Exception
{
    public const TYPE_GENERAL = 'task_feature_error';
    public const TYPE_NOT_TASK_OWNER = 'not_task_owner';
    public const TYPE_CREATE_TASK_FAILED = 'create_task_failed';
    public const TYPE_INVALID_TASK_STATUS_CHANGE = 'invalid_task_status_change';
    public const TYPE_INVALID_TASK_PRIORITY_CHANGE = 'invalid_task_priority_change';
    public const TYPE_DELETE_TASK_FAILED = 'delete_task_failed';

    /**
     * Create a new exception instance
     */
    public function __construct(
        private readonly ?int $userId = null,
        private readonly ?int $projectId = null,
        private readonly ?int $taskId = null,
        string $message = 'Task error',
        int $code = 0,
        private readonly string $type = self::TYPE_GENERAL,
        private readonly int $statusCode = 500,
        private readonly string $logLevel = 'error',
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Not task owner
     */
    public static function notTaskOwner(
        ?int $userId = null,
        ?int $taskId = null,
    ): self {
        return new self(
            userId: $userId,
            taskId: $taskId,
            message: 'You do not have permission to manage this task!',
            type: self::TYPE_NOT_TASK_OWNER,
            statusCode: 403,
            logLevel: 'warning',
        );
    }

    /**
     * Change task status failed
     */
    public static function createTaskFailed(
        ?int $userId = null,
        ?int $projectId = null,
        ?Throwable $previous = null,
    ): self {
        return new self(
            userId: $userId,
            projectId: $projectId,
            message: 'Failed to create task!',
            type: self::TYPE_CREATE_TASK_FAILED,
            statusCode: 500,
            logLevel: 'error',
            previous: $previous,
        );
    }

    /**
     * Change task status failed
     */
    public static function changeTaskStatusFailed(
        ?int $userId = null,
        ?int $taskId = null,
    ): self {
        return new self(
            userId: $userId,
            taskId: $taskId,
            message: 'Failed to change task status!',
            type: self::TYPE_INVALID_TASK_STATUS_CHANGE,
            statusCode: 500,
            logLevel: 'error',
        );
    }

    /**
     * Change task priority failed
     */
    public static function changeTaskPriorityFailed(
        ?int $userId = null,
        ?int $taskId = null,
    ): self {
        return new self(
            userId: $userId,
            taskId: $taskId,
            message: 'Failed to change task priority!',
            type: self::TYPE_INVALID_TASK_PRIORITY_CHANGE,
            statusCode: 500,
            logLevel: 'error',
        );
    }

    /**
     * Delete task failed
     */
    public static function deleteTaskFailed(
        ?int $taskId = null,
        ?Throwable $previous = null,
    ): self {
        return new self(
            taskId: $taskId,
            message: 'Failed to delete task!',
            type: self::TYPE_DELETE_TASK_FAILED,
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
            'user_id' => $this->userId,
            'project_id' => $this->projectId,
            'task_id' => $this->taskId,
            'previous' => $this->getPrevious()?->getMessage(),
        ]);

        $logLevel = $this->logLevel;

        Log::$logLevel('Task error', $context);
    }
}
