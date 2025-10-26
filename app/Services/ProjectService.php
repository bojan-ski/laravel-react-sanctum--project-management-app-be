<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\User;
use App\Models\Project;

class ProjectService
{
    public function getUserProjects(User $user): LengthAwarePaginator
    {
        $query = Project::query();

        $query->where(function ($q) use ($user) {
            $q->where('owner_id', $user->id)
                ->orWhereHas('members', function ($subQ) use ($user) {
                    $subQ->where('member_id', $user->id);
                });
        });

        return $query->with(['owner'])
            ->withCount(['members'])
            ->latest()
            ->paginate(2);
    }

    /**
     * create project
     */
    public function createProject(User $user, array $data): bool
    {
        try {
            DB::transaction(function () use ($user, $data) {
                // create project
                $project = Project::create([
                    // 'owner_id' => $user->id,
                    'title' => $data['title'],
                    'description' => $data['description'],
                    'deadline' => $data['deadline'],
                ]);

                // add owner as member
                $project->members()->attach($user->id, [
                    'joined_at' => now(),
                ]);
            });

            return true;
        } catch (\Throwable $th) {
            Log::error('Project creation failed', ['error' => $th->getMessage()]);

            return false;
        }
    }
}
