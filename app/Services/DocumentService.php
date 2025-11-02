<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use App\Models\Project;

class DocumentService
{
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
