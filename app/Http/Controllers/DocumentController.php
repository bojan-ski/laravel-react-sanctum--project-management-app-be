<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Project;
use App\Services\DocumentService;
use App\Traits\ApiResponse;

class DocumentController extends Controller
{
    use ApiResponse;

    public function __construct(private DocumentService $documentService) {}

    /**
     * Remove the specified resource from storage.
     */
    public function deleteFile(Project $project): JsonResponse
    {
        // call project service
        $response = $this->documentService->deleteProjectDocument($project);

        // return json
        if (!$response) {
            return $this->error('Delete project document error!', 500);
        }

        return $this->success(null, 'Project document deleted');
    }
}
