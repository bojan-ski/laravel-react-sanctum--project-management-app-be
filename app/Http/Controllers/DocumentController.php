<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Exceptions\DocumentException;
use App\Services\DocumentService;
use App\Traits\ApiResponse;
use App\Models\Document;

class DocumentController extends Controller
{
    use ApiResponse;

    public function __construct(protected readonly DocumentService $documentService) {}

    /**
     * Download a document.
     */
    public function download(
        Request $request,
        Document $document
    ): BinaryFileResponse {
        $user = $request->user();

        if (!$document->canView($user)) {
            throw DocumentException::cannotView(
                documentId: $document->id,
                userId: $user->id
            );
        }

        $fileInfo = $this->documentService->downloadDocument($document);

        return response()->download(
            $fileInfo['path'],
            $fileInfo['name']
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(
        Request $request,
        Document $document
    ): JsonResponse {
        $user = $request->user();

        if (!$document->canDelete($user)) {
            throw DocumentException::cannotDelete(
                documentId: $document->id,
                userId: $user->id
            );
        }

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
