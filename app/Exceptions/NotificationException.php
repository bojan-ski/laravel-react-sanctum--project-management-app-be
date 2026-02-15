<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Log;
use Throwable;
use Exception;
use App\Enums\NotificationType;

class NotificationException extends Exception
{
    public const TYPE_GENERAL = 'notification_feature_error';
    public const TYPE_CREATE_NOTIFICATION_FAILED = 'create_notification_failed';
    public const TYPE_MARK_READ_FAILED = 'mark_read_failed';
    public const TYPE_MARK_ALL_AS_READ_FAILED = 'mark_all_as_read_failed';
    public const TYPE_NOT_NOTIFICATION_OWNER = 'not_notification_owner';
    public const TYPE_NOT_INVITATION = 'not_invitation';
    public const TYPE_ALREADY_RESPONDED = 'already_responded';
    public const TYPE_PROJECT_NOT_FOUND = 'project_not_found';
    public const TYPE_ACCEPT_FAILED = 'accept_failed';
    public const TYPE_DECLINE_FAILED = 'decline_failed';

    /**
     * Create a new exception instance
     */
    public function __construct(
        private readonly ?int $notificationId = null,
        private readonly ?NotificationType $notificationType = null,
        private readonly ?int $senderId = null,
        private readonly ?int $userId = null,
        string $message = 'Notification error',
        int $code = 0,
        private readonly string $type = self::TYPE_GENERAL,
        private readonly int $statusCode = 500,
        private readonly string $logLevel = 'error',
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Create notification failed
     */
    public static function createNotificationFailed(
        ?NotificationType $notificationType = null,
        ?int $senderId = null,
        ?Throwable $previous = null,
    ): self {
        return new self(
            notificationType: $notificationType,
            senderId: $senderId,
            message: 'Failed to create notification!',
            type: self::TYPE_CREATE_NOTIFICATION_FAILED,
            statusCode: 500,
            logLevel: 'error',
            previous: $previous,
        );
    }

    /**
     * Not notification owner
     */
    public static function notNotificationOwner(
        ?int $notificationId = null,
        ?int $userId = null,
    ): self {
        return new self(
            notificationId: $notificationId,
            userId: $userId,
            message: 'You do not have permission to manage this notification!',
            type: self::TYPE_NOT_NOTIFICATION_OWNER,
            statusCode: 403,
            logLevel: 'warning',
        );
    }

    /**
     * Mark as read failed
     */
    public static function markAsReadFailed(
        ?int $notificationId = null
    ): self {
        return new self(
            notificationId: $notificationId,
            message: 'Failed to mark notification as read!',
            type: self::TYPE_MARK_READ_FAILED,
            statusCode: 500,
            logLevel: 'error'
        );
    }

    /**
     * Mark all notification as read failed
     */
    public static function markAllAsReadFailed(): self
    {
        return new self(
            message: 'Failed to mark all unread notification as read!',
            type: self::TYPE_MARK_ALL_AS_READ_FAILED,
            statusCode: 500,
            logLevel: 'error'
        );
    }

    /**
     * Not an invitation 
     */
    public static function notAnInvitation(
        ?int $notificationId = null,
    ): self {
        return new self(
            notificationId: $notificationId,
            message: 'Not an invitation notification!',
            type: self::TYPE_MARK_ALL_AS_READ_FAILED,
            statusCode: 422,
            logLevel: 'warning'
        );
    }

    /**
     * Not a pending invitation
     */
    public static function alreadyResponded(
        ?int $notificationId = null,
    ): self {
        return new self(
            notificationId: $notificationId,
            message: 'Already responded to the notification!',
            type: self::TYPE_ALREADY_RESPONDED,
            statusCode: 422,
            logLevel: 'warning'
        );
    }

    /**
     * Project not found error
     */
    public static function projectNotFound(
        ?int $notificationId = null,
    ): self {
        return new self(
            notificationId: $notificationId,
            message: 'Project not found!',
            type: self::TYPE_PROJECT_NOT_FOUND,
            statusCode: 410,
            logLevel: 'warning'
        );
    }

    /**
     * Accept invitation failed
     */
    public static function acceptInvitationFailed(
        ?int $notificationId = null,
        ?int $userId = null,
    ): self {
        return new self(
            notificationId: $notificationId,
            userId: $userId,
            message: 'Failed to accept invitation!',
            type: self::TYPE_ACCEPT_FAILED,
            statusCode: 500,
            logLevel: 'error'
        );
    }

    /**
     * Decline invitation failed
     */
    public static function declineInvitationFailed(
        ?int $notificationId = null,
        ?int $userId = null,
    ): self {
        return new self(
            notificationId: $notificationId,
            userId: $userId,
            message: 'Failed to decline invitation!',
            type: self::TYPE_DECLINE_FAILED,
            statusCode: 500,
            logLevel: 'error'
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
            'notification_id' => $this->notificationId,
            'notification_type' => $this->notificationType,
            'sender_id' => $this->senderId,
            'user_id' => $this->userId,
            'previous' => $this->getPrevious()?->getMessage(),
        ]);

        $logLevel = $this->logLevel;

        Log::$logLevel('Notification error', $context);
    }
}
