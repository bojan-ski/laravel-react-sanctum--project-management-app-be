<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
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
            'priority' => $this->priority,
            'due_date' => $this->due_date?->format('Y-m-d'),
            'is_overdue' => $this->isOverdue(),
            'project' => [
                'id' => $this->project->id,
                'title' => $this->project->title,
                'status' => $this->project->status,
                'is_active' => $this->project->isProjectActive(),
            ],
            'creator' => [
                'id' => $this->creator->id,
                'name' => $this->creator->name,
                'email' => $this->assignee->email,
                'avatar' => $this->creator->avatar ?? null,
            ],
            'assignee' => $this->assignee ? [
                'id' => $this->assignee->id,
                'name' => $this->assignee->name,
                'email' => $this->assignee->email,
                'avatar' => $this->assignee->avatar ?? null,
            ] : null,
            'task_in_progress' => $this->isTaskInProgress(),
            'activities' => $this->when(
                $this->relationLoaded('activities'),
                fn() => TaskActivityResource::collection($this->activities)
            ),
            'is_creator' => $this->isCreator($request->user()),
            'is_assignee' => $this->isAssignee($request->user()),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
