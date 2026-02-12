<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Log;
use Throwable;
use Exception;

class ProjectException extends Exception
{
    public const TYPE_GENERAL = 'project_feature_error';
    public const TYPE_CREATE_FAILED = 'create_failed';
    public const TYPE_UPDATE_FAILED = 'update_failed';
    public const TYPE_DELETE_FAILED = 'delete_failed';
    public const TYPE_NOT_OWNER = 'not_owner';
    public const TYPE_NOT_MEMBER = 'not_member';
    public const TYPE_INVALID_STATUS_CHANGE = 'invalid_status_change';

    /**
     * Create a new exception instance
     */
    public function __construct(
        private readonly ?int $userId = null,
        private readonly ?int $projectId = null,
        string $message = 'Project error',
        int $code = 0,
        private readonly string $type = self::TYPE_GENERAL,
        private readonly int $statusCode = 500,
        private readonly string $logLevel = 'error',
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Not project owner
     */
    public static function notOwner(
        ?int $userId = null,
        ?int $projectId = null,
    ): self {
        return new self(
            userId: $userId,
            projectId: $projectId,
            message: 'You do not have permission to manage this project!',
            type: self::TYPE_NOT_OWNER,
            statusCode: 403,
            logLevel: 'warning',
        );
    }

    /**
     * Not project member
     */
    public static function notMember(
        ?int $userId = null,
        ?int $projectId = null,
    ): self {
        return new self(
            userId: $userId,
            projectId: $projectId,
            message: 'You are not a member of this project!',
            type: self::TYPE_NOT_MEMBER,
            statusCode: 403,
            logLevel: 'warning',
        );
    }

    /**
     * Create project failed
     */
    public static function createProjectFailed(
        ?int $userId = null,
        ?Throwable $previous = null,
    ): self {
        return new self(
            userId: $userId,
            message: 'Failed to create project!',
            type: self::TYPE_CREATE_FAILED,
            statusCode: 500,
            logLevel: 'error',
            previous: $previous,
        );
    }

    /**
     * Update project failed
     */
    public static function updateProjectFailed(
        ?int $projectId = null,
        ?Throwable $previous = null,
    ): self {
        return new self(
            projectId: $projectId,
            message: 'Failed to update project!',
            type: self::TYPE_UPDATE_FAILED,
            statusCode: 500,
            logLevel: 'error',
            previous: $previous,
        );
    }

    /**
     * Delete project failed
     */
    public static function deleteProjectFailed(
        ?int $projectId = null,
        ?Throwable $previous = null,
    ): self {
        return new self(
            projectId: $projectId,
            message: 'Failed to delete project!',
            type: self::TYPE_DELETE_FAILED,
            statusCode: 500,
            logLevel: 'error',
            previous: $previous,
        );
    }

    /**
     * Change project status failed
     */
    public static function changeProjectStatusFailed(
        ?int $projectId = null,
    ): self {
        return new self(
            projectId: $projectId,
            message: 'Failed to change project status!',
            type: self::TYPE_INVALID_STATUS_CHANGE,
            statusCode: 500,
            logLevel: 'error',
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
            'previous' => $this->getPrevious()?->getMessage(),
        ]);

        $logLevel = $this->logLevel;

        Log::$logLevel('Project error', $context);
    }
}
