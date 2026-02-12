<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Log;
use Throwable;
use Exception;

class ProjectMemberException extends Exception
{
    public const TYPE_GENERAL = 'project_member_feature_error';
    public const TYPE_MAX_MEMBERS_REACHED = 'max_members_reached';
    public const TYPE_INVITE_FAILED = 'invite_failed';
    public const TYPE_SELF_INVITE = 'self_invite';
    public const TYPE_ALREADY_INVITED = 'already_invited';
    public const TYPE_ALREADY_MEMBER = 'already_member';
    public const TYPE_NOT_MEMBER = 'not_member';
    public const TYPE_PROJECT_OWNER = 'project_owner';
    public const TYPE_LEAVE_FAILED = 'remove_failed';
    public const TYPE_REMOVE_FAILED = 'remove_failed';

    /**
     * Create a new exception instance
     */
    public function __construct(
        private readonly ?int $userId = null,
        private readonly ?int $projectId = null,
        string $message = 'Project member error',
        int $code = 0,
        private readonly string $type = self::TYPE_GENERAL,
        private readonly int $statusCode = 500,
        private readonly string $logLevel = 'error',
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Invite user as project member failed
     */
    public static function maxMembersReached(
        ?int $projectId = null,
    ): self {
        return new self(
            projectId: $projectId,
            message: 'Max member per project reached!',
            type: self::TYPE_MAX_MEMBERS_REACHED,
            statusCode: 422,
            logLevel: 'warning',
        );
    }

    /**
     * Can not invite self as project member
     */
    public static function cannotInviteSelf(
        ?int $projectId = null,
        ?int $userId = null,
    ): self {
        return new self(
            projectId: $projectId,
            userId: $userId,
            message: 'Can not invite self!',
            type: self::TYPE_SELF_INVITE,
            statusCode: 422,
            logLevel: 'warning',
        );
    }

    /**
     * User already project member 
     */
    public static function alreadyMember(
        ?int $projectId = null,
        ?int $userId = null,
    ): self {
        return new self(
            projectId: $projectId,
            userId: $userId,
            message: 'User is already a member of this project!',
            type: self::TYPE_SELF_INVITE,
            statusCode: 422,
            logLevel: 'warning',
        );
    }

    /**
     * User has pending invitation 
     */
    public static function alreadyInvited(
        ?int $projectId = null,
        ?int $userId = null,
    ): self {
        return new self(
            projectId: $projectId,
            userId: $userId,
            message: 'User has already been invited to this project!',
            type: self::TYPE_ALREADY_INVITED,
            statusCode: 422,
            logLevel: 'warning',
        );
    }


    /**
     * Invite user as project member failed
     */
    public static function inviteUsersFailed(
        ?int $projectId = null,
        ?Throwable $previous = null,
    ): self {
        return new self(
            projectId: $projectId,
            message: 'Failed to send invitation!',
            type: self::TYPE_INVITE_FAILED,
            statusCode: 500,
            logLevel: 'error',
            previous: $previous,
        );
    }

    /**
     * User is not project member 
     */
    public static function notMember(
        ?int $projectId = null,
        ?int $userId = null,
    ): self {
        return new self(
            projectId: $projectId,
            userId: $userId,
            message: 'User is not a project member!',
            type: self::TYPE_NOT_MEMBER,
            statusCode: 422,
            logLevel: 'warning',
        );
    }

    /**
     * Can not remove self as project member 
     */
    public static function projectOwner(
        ?int $projectId = null,
        ?int $userId = null,
    ): self {
        return new self(
            projectId: $projectId,
            userId: $userId,
            message: 'Project owner!',
            type: self::TYPE_PROJECT_OWNER,
            statusCode: 422,
            logLevel: 'warning',
        );
    }

    /**
     * Leave project failed
     */
    public static function leaveProjectFailed(
        ?int $projectId = null,
        ?int $userId = null,
        ?Throwable $previous = null,
    ): self {
        return new self(
            projectId: $projectId,
            userId: $userId,
            message: 'Failed to leave project!',
            type: self::TYPE_LEAVE_FAILED,
            statusCode: 500,
            logLevel: 'error',
            previous: $previous,
        );
    }

    /**
     * Remove project member failed
     */
    public static function removeMemberFailed(
        ?int $projectId = null,
        ?int $userId = null,
        ?Throwable $previous = null,
    ): self {
        return new self(
            projectId: $projectId,
            userId: $userId,
            message: 'Failed to remove member!',
            type: self::TYPE_REMOVE_FAILED,
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
            'previous' => $this->getPrevious()?->getMessage(),
        ]);

        $logLevel = $this->logLevel;

        Log::$logLevel('Project error', $context);
    }
}
