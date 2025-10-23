<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Project;

class ProjectService
{
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
