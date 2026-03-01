<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Log;
use Throwable;
use Exception;

class UserException extends Exception
{
    public const TYPE_GENERAL = 'user_feature_error';
    public const TYPE_ADD_USER_FAILED = 'add_user_failed';
    public const TYPE_DELETE_USER_FAILED = 'delete_user_failed';

    /**
     * Create a new exception instance
     */
    public function __construct(
        private readonly ?int $userId = null,
        private readonly ?string $email = null,
        string $message = 'User error',
        int $code = 0,
        private readonly string $type = self::TYPE_GENERAL,
        private readonly int $statusCode = 500,
        private readonly string $logLevel = 'error',
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Add new user failed
     */
    public static function addNewUserFailed(
        ?string $email = null,
        ?Throwable $previous = null,
    ): self {
        return new self(
            email: $email,
            message: 'Failed to add a new user!',
            type: self::TYPE_ADD_USER_FAILED,
            statusCode: 500,
            logLevel: 'error',
            previous: $previous,
        );
    }

    /**
     * Delete user failed
     */
    public static function deleteUserFailed(
        ?int $userId = null,
        ?Throwable $previous = null,
    ): self {
        return new self(
            userId: $userId,
            message: 'Failed to delete user!',
            type: self::TYPE_DELETE_USER_FAILED,
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
            'email' => $this->email,
            'previous' => $this->getPrevious()?->getMessage(),
        ]);

        $logLevel = $this->logLevel;

        Log::$logLevel('User error', $context);
    }
}
