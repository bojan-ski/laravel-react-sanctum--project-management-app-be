<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Models\Project;

class DocumentService
{
    /**
     * Upload project document
     */
    public function uploadDocument(
        UploadedFile $file,
        ?int $projectId
    ): string {
        $directory = "documents/projects/{$projectId}";

        return $file->store($directory, 'public');
    }


    /**
     * delete project document
     */
    public function deleteProjectDocument(Project $project): bool
    {
        try {
            // delete from storage
            Storage::disk('public')->delete($project->document_path);

            // update project 
            $project->update(['document_path' => null]);

            return true;
        } catch (\Throwable $th) {
            Log::error('Project document deletion failed', [
                'error' => $th->getMessage()
            ]);

            return false;
        }
    }
}
