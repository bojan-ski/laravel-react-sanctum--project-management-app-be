<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Services\ProjectMemberService;

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
            'document' => $this->document ? new DocumentResource($this->document) : null,
            'owner' => [
                'name' => $this->owner->name,
                'avatar' => $this->owner->avatar,
            ],
            'is_owner' => $this->isOwner($request->user()),
            'members' => $this->members->map(fn($member) => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'avatar' => $member->avatar ?? null,
                'is_owner' => $this->isOwner($member),
                'joined_at' => $member->pivot->joined_at ?? null,
            ]),
            'is_member' => $this->isMember($request->user()),
            'members_limit' => ProjectMemberService::MAX_MEMBERS_PER_PROJECT,
            'tasks' => $this->when(
                $this->relationLoaded('tasks'),
                fn() => TaskCardResource::collection($this->tasks)
            ),
            'statistics' => $this->getStatistics(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
