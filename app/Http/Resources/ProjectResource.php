<?php

namespace App\Http\Resources;

use App\Services\ProjectMemberService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
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
            'owner' => [
                'name' => $this->owner->name,
                'avatar' => $this->owner->avatar,
            ],
            'is_owner' => $this->isOwner($request->user()),
            'members' => $this->members->map(fn($member) => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'avatar' => $member->avatar,
                'is_owner' => $this->isOwner($member),
                'joined_at' => $member->pivot->joined_at ?? null,
            ]),
            'members_limit' => ProjectMemberService::MAX_MEMBERS_PER_PROJECT,
            'tasks' => $this->tasks,
            'statistics' => $this->getStatistics(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
