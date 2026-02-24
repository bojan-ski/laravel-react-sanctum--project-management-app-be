<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Exceptions\DocumentException;
use App\Models\Document;
use App\Models\User;

class DocumentService
{
    /**
     * Download document
     */
    public function downloadDocument(Document $document): array
    {
        return [
            'path' => Storage::disk('public')->path($document->doc_path),
            'name' => $document->doc_name,
        ];
    }

    /**
     * Generate storage path for document
     */
    private function generateStoragePath(
        Model $documentable,
        UploadedFile $file,
        ?string $customPath = null
    ): string {
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = Str::uuid() . '.' . $extension;

        if ($customPath) {
            return "{$customPath}/{$filename}";
        }

        $modelType = strtolower(class_basename($documentable));
        $modelId = $documentable->getKey();

        return "documents/{$modelType}/{$modelId}/{$filename}";
    }

    /**
     * Upload document
     */
    public function uploadDocument(
        User $uploader,
        Model $documentable,
        UploadedFile $file,
        ?string $storagePath = null
    ): void {
        $resolvedPath = null;

        try {
            DB::transaction(function () use ($file, $documentable, $uploader, $storagePath, &$resolvedPath) {
                $resolvedPath = $this->generateStoragePath($documentable, $file, $storagePath);

                $path = $file->storeAs(
                    dirname($resolvedPath),
                    basename($resolvedPath),
                    'public'
                );

                Document::create([
                    'documentable_type' => get_class($documentable),
                    'documentable_id' => $documentable->getKey(),
                    'uploaded_by' => $uploader->id,
                    'doc_name' => $file->getClientOriginalName(),
                    'doc_path' => $path,
                ]);
            });
        } catch (\Throwable $e) {
            if ($resolvedPath) {
                Storage::disk('public')->delete($resolvedPath);
            }

            throw DocumentException::uploadDocumentFailed(
                documentableType: get_class($documentable),
                documentableId: $documentable->getKey(),
                previous: $e
            );
        }
    }

    /**
     * Delete document path
     */
    public function deleteDocumentPath(Document $document): void
    {
        try {
            DB::transaction(function () use ($document) {
                Storage::disk('public')->delete($document->doc_path);

                $document->delete();
            });
        } catch (\Throwable $e) {
            throw DocumentException::deleteDocumentFailed(
                documentableId: $document->id,
                previous: $e
            );
        }
    }

    /**
     * Delete document directory
     */
    public function deleteDocumentDirectory(Model $documentable): void
    {
        $modelType = strtolower(class_basename($documentable));
        $modelId = $documentable->getKey();

        try {
            Storage::disk('public')->deleteDirectory("documents/{$modelType}/{$modelId}");
        } catch (\Throwable $e) {
            throw DocumentException::deleteDocumentFailed(
                documentableId: $modelId,
                previous: $e
            );
        }
    }
}
