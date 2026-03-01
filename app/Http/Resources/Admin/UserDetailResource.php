<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserDetailResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'avatar' => $this->avatar ?? null,
            'role' => $this->role,
            'is_admin' => $this->isAdmin(),
            'owned_projects_count' => $this->owned_projects_count ?? 0,
            'member_projects_count' => $this->member_projects_count ?? 0,
            'total_projects' => ($this->owned_projects_count ?? 0) + ($this->member_projects_count ?? 0),
            'recent_projects' => $this->whenLoaded(
                'ownedProjects',
                fn() =>
                $this->ownedProjects->map(fn($project) => [
                    'id' => $project->id,
                    'title' => $project->title,
                    'status' => $project->status,
                    'created_at_human' => $project->created_at->diffForHumans(),
                ])
            ),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}