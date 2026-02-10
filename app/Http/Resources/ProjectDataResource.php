<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'deadline' => $this->deadline?->format('Y-m-d'),
            'document'   => $this->document ? [
                'id'                => $this->document->id,
                'documentable_type' => $this->document->documentable_type,
                'documentable_id'   => $this->document->documentable_id,
                'doc_name'          => $this->document->doc_name,
                'doc_path'          => $this->document->doc_path,
                'created_at'        => $this->document->created_at->toIso8601String(),
                'updated_at'        => $this->document->updated_at->toIso8601String(),
            ] : null,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
