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
        UploadedFile $file
    ): string {
        $modelType = strtolower(class_basename($documentable));
        $modelId = $documentable->getKey();
        $extension = strtolower($file->getClientOriginalExtension());
        $filename = Str::uuid() . '.' . $extension;

        return "documents/{$modelType}/{$modelId}/{$filename}";
    }

    /**
     * Upload document
     */
    public function uploadDocument(
        User $uploader,
        Model $documentable,
        UploadedFile $file,
    ): void {
        try {
            DB::transaction(function () use ($file, $documentable, $uploader) {
                $storagePath = $this->generateStoragePath($documentable, $file);

                $path = $file->storeAs(
                    dirname($storagePath),
                    basename($storagePath),
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
            if ($storagePath) {
                Storage::disk('public')->delete($storagePath);
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
                documentId: $document->id,
                previous: $e
            );
        }
    }

    /**
     * Delete document directory
     */
    private function deleteDocumentDirectory(Document $document): void
    {
        try {
            $documentable = $document->documentable;

            $documentableType = strtolower(class_basename($documentable));
            $documentableId = $documentable->getKey();

            Storage::disk('public')->deleteDirectory("documents/{$documentableType}/{$documentableId}");
        } catch (\Throwable $e) {
            throw DocumentException::deleteDocumentFailed(
                documentId: $documentableId,
                previous: $e
            );
        }
    }

    /**
     * Delete document
     */
    public function deleteDocument(Document $document): void
    {
        try {
            $this->deleteDocumentDirectory($document);

            $document->delete();
        } catch (\Throwable $e) {
            throw DocumentException::deleteDocumentFailed(
                documentId: $document->id,
                previous: $e
            );
        }
    }
}
