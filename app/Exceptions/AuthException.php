<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Log;
use Throwable;
use Exception;

class AuthException extends Exception
{
    public const TYPE_GENERAL = 'auth_feature_error';
    public const TYPE_INVALID_CREDENTIALS = 'invalid_credentials';
    public const TYPE_LOGOUT_FAILED = 'logout_failed';

    /**
     * Create a new exception instance
     */
    public function __construct(
        private readonly ?string $email = null,
        string $message = 'Auth error',
        int $code = 0,
        private readonly string $type = self::TYPE_GENERAL,
        private readonly int $statusCode = 400,
        private readonly string $logLevel = 'error',
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * Invalid login credentials
     */
    public static function invalidCredentials(
        ?string $email = null,
        ?Throwable $previous = null,
    ): self {
        return new self(
            email: $email,
            message: 'Invalid credentials!',
            type: self::TYPE_INVALID_CREDENTIALS,
            statusCode: 401,
            logLevel: 'warning',
            previous: $previous,
        );
    }

    /**
     * Logout failure
     */
    public static function logoutFailed(
        ?string $email = null,
        ?Throwable $previous = null
    ): self {
        return new self(
            email: $email,
            message: 'Failed to logout!',
            type: self::TYPE_LOGOUT_FAILED,
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
            'email' => $this->email,
        ]);

        $logLevel = $this->logLevel;

        Log::$logLevel('Auth error', $context);
    }
}
