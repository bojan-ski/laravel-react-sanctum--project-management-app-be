<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Log;
use Throwable;
use Exception;

class AvatarException extends Exception
{
    public const TYPE_GENERAL = 'avatar_feature_error';
    public const TYPE_AVATAR_UPLOAD_FAILED = 'avatar_upload_failed';
    public const TYPE_AVATAR_PROCESS_FAILED = 'avatar_process_failed';
    public const TYPE_AVATAR_DELETE_FAILED = 'avatar_delete_failed';

    /**
     * Create a new exception instance
     */
    public function __construct(
        private readonly ?int $userId = null,
        private readonly ?string $filename = null,
        private readonly ?int $avatarId = null,
        string $message = 'Avatar error',
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
        ?string $filename = null,
        ?Throwable $previous = null,
    ): self {
        return new self(
            filename: $filename,
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
        ?int $avatarId = null,
        ?Throwable $previous = null,
    ): self {
        return new self(
            avatarId: $avatarId,
            message: 'Failed to delete old avatar!',
            type: self::TYPE_AVATAR_DELETE_FAILED,
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
            'filename' => $this->filename,
            'avatar_id' => $this->avatarId,
            'previous' => $this->getPrevious()?->getMessage(),
        ]);

        $logLevel = $this->logLevel;

        Log::$logLevel('Avatar error', $context);
    }
}
