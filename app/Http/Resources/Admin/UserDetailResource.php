<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\ProjectCardResource;

class UserDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $ownedCount = (int) ($this->owned_projects_count ?? 0);
        $memberCount = (int) ($this->member_projects_count ?? 0);
        
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar ?? null,
            'role' => $this->role,
            'is_admin' => $this->isAdmin(),
            'owned_projects_count' => $ownedCount,
            'member_projects_count' => $memberCount,
            'total_projects' => $ownedCount + $memberCount,
            'recent_projects' => ProjectCardResource::collection($this->whenLoaded('projects')),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}