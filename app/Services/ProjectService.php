<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\User;
use App\Models\Project;
use App\Enums\ProjectStatus;

class ProjectService
{
    public function getUserProjects(
        User $user,
        ?string $ownership = 'all',
        ?string $status = null
    ): LengthAwarePaginator {
        $query = Project::query();

        // filter by project ownership
        switch ($ownership) {
            case 'owner':
                $query->where('owner_id', $user->id);
                break;

            case 'member':
                $query->whereHas('members', function ($q) use ($user) {
                    $q->where('member_id', $user->id);
                })->where('owner_id', '!=', $user->id);
                break;

            case 'all':
            default:
                $query->where(function ($q) use ($user) {
                    $q->where('owner_id', $user->id)
                        ->orWhereHas('members', function ($subQ) use ($user) {
                            $subQ->where('member_id', $user->id);
                        });
                });
                break;
        }

        // filter by project status
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        return $query->with(['owner'])
            ->withCount(['members'])
            ->latest()
            ->paginate(2);
    }

    /**
     * create project
     */
    public function createProject(
        User $user,
        array $formData,
        ?UploadedFile $file = null
    ): bool {
        try {
            DB::transaction(function () use ($user, $formData, $file) {
                // if file exists
                if ($file) {
                    $filePath = $file->store('documents', 'public');
                    $formData['document_path'] = $filePath;
                }

                // create project
                $project = Project::create([
                    // 'owner_id' => $user->id,
                    'title' => $formData['title'],
                    'description' => $formData['description'],
                    'deadline' => $formData['deadline'],
                    'document_path' => $formData['document_path'] ?? null
                ]);

                // TEST - REMOVE ON APP COMPLETION
                $project->update(['status' => ProjectStatus::CLOSED]);
                // TEST - REMOVE ON APP COMPLETION

                // add owner as member
                $project->members()->attach($user->id, [
                    'joined_at' => now(),
                ]);
            });

            return true;
        } catch (\Throwable $th) {
            Log::error('Project creation failed', [
                'error' => $th->getMessage()
            ]);

            return false;
        }
    }
}
