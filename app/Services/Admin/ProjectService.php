<?php

namespace App\Services\Admin;

use App\Enums\ProjectStatus;
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

    /**
     * Get app project statistics.
     */
    public function getStats(): array
    {
        $stats = Project::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending', [ProjectStatus::PENDING->value])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active', [ProjectStatus::ACTIVE->value])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed', [ProjectStatus::COMPLETED->value])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as closed', [ProjectStatus::CLOSED->value])
            ->selectRaw('SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as created_this_month', [now()->startOfMonth()])
            ->selectRaw('SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as created_this_week', [now()->startOfWeek()])
            ->first();

        return [
            'total' => (int) $stats->total,
            'by_status' => [
                'pending' => (int) $stats->pending,
                'active' => (int) $stats->active,
                'completed' => (int) $stats->completed,
                'closed' => (int) $stats->closed,
            ],
            'created_this_month' => (int) $stats->created_this_month,
            'created_this_week' => (int) $stats->created_this_week,
        ];
    }
}
