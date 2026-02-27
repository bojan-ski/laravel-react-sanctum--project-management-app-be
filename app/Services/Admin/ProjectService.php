<?php

namespace App\Services\Admin;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Enums\TaskStatus;
use App\Models\Project;

class ProjectService
{
    /**
     * Get user projects with filter feature
     */
    public function getAllProjects(
        ?string $search = null,
        int $perPage = 12
    ): LengthAwarePaginator {
        $query = Project::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhereHas('owner', function ($ownerQuery) use ($search) {
                        $ownerQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        return $query->with(['owner:id,name,email,avatar'])
            ->withCount([
                'members',
                'tasks',
                'tasks as completed_tasks_count' => function ($q) {
                    $q->where('status', TaskStatus::DONE);
                },
            ])
            ->latest()
            ->paginate($perPage);
    }
}
