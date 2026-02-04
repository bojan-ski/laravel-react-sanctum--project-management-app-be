<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Log;
use Throwable;
use Exception;

class ProfileException extends Exception
{
    public const TYPE_GENERAL = 'profile_feature_error';
    public const TYPE_AVATAR_UPLOAD_FAILED = 'avatar_upload_failed';
    public const TYPE_AVATAR_PROCESS_FAILED = 'avatar_process_failed';
    public const TYPE_AVATAR_DELETE_FAILED = 'avatar_delete_failed';
    public const TYPE_PASSWORD_INCORRECT = 'password_incorrect';
    public const TYPE_PASSWORD_CHANGE_FAILED = 'password_change_failed';
    public const TYPE_ACCOUNT_DELETE_FAILED = 'account_delete_failed';

    /**
     * Create a new exception instance
     */
    public function __construct(
        private readonly ?int $userId = null,
        string $message = 'Profile error',
        int $code = 0,
        private readonly string $type = self::TYPE_GENERAL,
        private readonly int $statusCode = 500,
        private readonly string $logLevel = 'error',
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Avatar upload failed
     */
    public static function avatarUploadFailed(
        ?int $userId = null,
        ?Throwable $previous = null,
    ): self {
        return new self(
            userId: $userId,
            message: 'Failed to upload avatar!',
            type: self::TYPE_AVATAR_UPLOAD_FAILED,
            statusCode: 500,
            logLevel: 'error',
            previous: $previous,
        );
    }

    /**
     * Avatar processing failed
     */
    public static function avatarProcessFailed(
        ?int $userId = null,
        ?Throwable $previous = null,
    ): self {
        return new self(
            userId: $userId,
            message: 'Failed to process avatar image!',
            type: self::TYPE_AVATAR_PROCESS_FAILED,
            statusCode: 422,
            logLevel: 'warning',
            previous: $previous,
        );
    }

    /**
     * Avatar deletion failed
     */
    public static function avatarDeleteFailed(
        ?int $userId = null,
        ?Throwable $previous = null,
    ): self {
        return new self(
            userId: $userId,
            message: 'Failed to delete old avatar!',
            type: self::TYPE_AVATAR_DELETE_FAILED,
            statusCode: 500,
            logLevel: 'error',
            previous: $previous,
        );
    }

    /**
     * Current password is incorrect
     */
    public static function passwordIncorrect(
        ?int $userId = null,
    ): self {
        return new self(
            userId: $userId,
            message: 'Password is incorrect!',
            type: self::TYPE_PASSWORD_INCORRECT,
            statusCode: 401,
            logLevel: 'warning',
        );
    }

    /**
     * Password change failed
     */
    public static function passwordChangeFailed(
        ?int $userId = null,
        ?Throwable $previous = null,
    ): self {
        return new self(
            userId: $userId,
            message: 'Failed to change password!',
            type: self::TYPE_PASSWORD_CHANGE_FAILED,
            statusCode: 500,
            logLevel: 'error',
            previous: $previous,
        );
    }

    /**
     * Account deletion failed
     */
    public static function accountDeleteFailed(
        ?int $userId = null,
        ?Throwable $previous = null,
    ): self {
        return new self(
            userId: $userId,
            message: 'Failed to delete account!',
            type: self::TYPE_ACCOUNT_DELETE_FAILED,
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
            'message' => $this->getMessage(),
            'previous' => $this->getPrevious()?->getMessage(),
            'type' => $this->type,
            'user_id' => $this->userId,
        ]);

        $logLevel = $this->logLevel;

        Log::$logLevel('Auth error', $context);
    }
}
