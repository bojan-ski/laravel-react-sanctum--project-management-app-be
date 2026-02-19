<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Log;
use Throwable;
use Exception;

class TaskActivityException extends Exception
{
    public const TYPE_GENERAL = 'task_activity_feature_error';
    public const TYPE_LOG_TASK_ACTIVITY_FAILED = 'task_activity_feature_error';

    /**
     * Create a new exception instance
     */
    public function __construct(
        private readonly ?int $userId = null,
        private readonly ?int $taskId = null,
        private readonly ?string $activity = null,
        string $message = 'Task activity error',
        int $code = 0,
        private readonly string $type = self::TYPE_GENERAL,
        private readonly int $statusCode = 500,
        private readonly string $logLevel = 'error',
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Log task activity failed
     */
    public static function logTaskActivityFailed(
        ?int $userId = null,
        ?int $taskId = null,
        ?string $activity = null,
        ?Throwable $previous = null,
    ): self {
        return new self(
            userId: $userId,
            taskId: $taskId,
            activity: $activity,
            message: 'Failed to log task activity!',
            type: self::TYPE_LOG_TASK_ACTIVITY_FAILED,
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
            'task_id' => $this->taskId,
            'activity' => $this->activity,
            'previous' => $this->getPrevious()?->getMessage(),
        ]);

        $logLevel = $this->logLevel;

        Log::$logLevel('Task activity error', $context);
    }
}
