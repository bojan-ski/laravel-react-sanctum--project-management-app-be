<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Log;
use Throwable;
use Exception;
use Illuminate\Http\JsonResponse;

class DocumentException extends Exception
{
    public const TYPE_GENERAL = 'document_feature_error';
    public const TYPE_DOCUMENT_UPLOAD_FAILED = 'document_upload_failed';
    public const TYPE_DOCUMENT_DELETE_FAILED = 'document_delete_failed';
    public const TYPE_CANNOT_VIEW = 'cannot_view';
    public const TYPE_CANNOT_DELETE = 'cannot_delete';

    /**
     * Create a new exception instance
     */
    public function __construct(
        private readonly ?string $documentableType = null,
        private readonly ?int $documentableId = null,
        private readonly ?int $documentId = null,
        private readonly ?int $userId = null,
        string $message = 'Document error',
        int $code = 0,
        private readonly string $type = self::TYPE_GENERAL,
        private readonly int $statusCode = 500,
        private readonly string $logLevel = 'error',
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    /**
     * User has no permission to view document
     */
    public static function cannotView(
        ?string $documentId = null,
        ?int $userId = null,
    ): self {
        return new self(
            documentId: $documentId,
            userId: $userId,
            message: 'You do not have permission to view this document!',
            type: self::TYPE_DOCUMENT_UPLOAD_FAILED,
            statusCode: 403,
            logLevel: 'warning',
        );
    }

    /**
     * User has no permission to delete document
     */
    public static function cannotDelete(
        ?string $documentId = null,
        ?int $userId = null,
    ): self {
        return new self(
            documentId: $documentId,
            userId: $userId,
            message: 'You do not have permission to delete this document!',
            type: self::TYPE_CANNOT_DELETE,
            statusCode: 403,
            logLevel: 'warning',
        );
    }

    /**
     * Upload document failed
     */
    public static function uploadDocumentFailed(
        ?string $documentableType = null,
        ?int $documentableId = null,
        ?Throwable $previous = null,
    ): self {
        return new self(
            documentableType: $documentableType,
            documentableId: $documentableId,
            message: 'Failed to upload document!',
            type: self::TYPE_DOCUMENT_UPLOAD_FAILED,
            statusCode: 500,
            logLevel: 'error',
            previous: $previous,
        );
    }

    /**
     * Delete document failed
     */
    public static function deleteDocumentFailed(
        ?int $documentableId = null,
        ?Throwable $previous = null,
    ): self {
        return new self(
            documentableId: $documentableId,
            message: 'Failed to delete document!',
            type: self::TYPE_DOCUMENT_DELETE_FAILED,
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
     * Render the exception as an HTTP response
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $this->getMessage(),
        ], $this->statusCode);
    }

    /**
     * Report the exception
     */
    public function report(): void
    {
        $context = array_filter([
            'type' => $this->type,
            'message' => $this->getMessage(),
            'documentable_type' => $this->documentableType,
            'documentable_id' => $this->documentableId,
            'document_id' => $this->documentId,
            'user_id' => $this->userId,
            'previous' => $this->getPrevious()?->getMessage(),
        ]);

        $logLevel = $this->logLevel;

        Log::$logLevel('Project error', $context);
    }
}
