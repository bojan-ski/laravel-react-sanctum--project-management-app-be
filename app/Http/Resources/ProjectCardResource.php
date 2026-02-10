<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectCardResource extends JsonResource
{
    /**
     * Truncate description
     */
    private function truncateDescription(
        string $description,
        int $length
    ): string {
        if (strlen($description) <= $length) {
            return $description;
        }

        return substr($description, 0, $length) . '...';
    }

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
            // 'description' => $this->truncateDescription($this->description, 150),
            'status' => $this->status,
            'deadline' => $this->deadline?->format('Y-m-d'),
            'is_owner' => $this->isOwner($request->user()),
            'owner' => [
                'name' => $this->owner->name,
                'avatar' => $this->owner->avatar,
            ],
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
