<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Exceptions\DocumentException;
use App\Services\DocumentService;
use App\Traits\ApiResponse;
use App\Models\Document;

class DocumentController extends Controller
{
    use ApiResponse;

    public function __construct(protected readonly DocumentService $documentService) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Document $document): JsonResponse
    {
        try {
            $this->documentService->deleteDocument($document);

            return $this->success(message: 'Document deleted');
        } catch (DocumentException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: $e->getStatusCode()
            );
        }
    }
}
