<?php

namespace App\Exceptions;

use Illuminate\Support\Facades\Log;
use Throwable;
use Exception;

class DocumentException extends Exception
{
    public const TYPE_GENERAL = 'document_feature_error';
    public const TYPE_DOCUMENT_UPLOAD_FAILED = 'document_upload_failed';
    public const TYPE_DOCUMENT_DELETE_FAILED = 'document_delete_failed';

    /**
     * Create a new exception instance
     */
    public function __construct(
        private readonly ?string $documentableType = null,
        private readonly ?int $documentableId = null,
        private readonly ?int $documentId = null,
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
        ?int $documentId = null,
        ?Throwable $previous = null,
    ): self {
        return new self(
            documentId: $documentId,
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
            'previous' => $this->getPrevious()?->getMessage(),
        ]);

        $logLevel = $this->logLevel;

        Log::$logLevel('Project error', $context);
    }
}
