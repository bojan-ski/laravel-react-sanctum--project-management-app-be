<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\User;
use App\Models\Project;
use App\Enums\ProjectStatus;

class ProjectService
{
    public function __construct(private NotificationService $notificationService) {}

    /**
     * Get user projects with filter feature
     */
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

        // return projects
        return $query->with(['owner'])
            ->withCount(['members'])
            ->latest()
            ->paginate(2);
    }

    /**
     * Create project
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

    /**
     * Get selected project details
     */
    public function getProjectDetails(Project $project): Project
    {
        return $project->load([
            'owner',
            'members',
        ])->loadCount(['members']);
    }

    /**
     * Update project
     */
    public function updateProject(
        Project $project,
        array $formData,
        ?UploadedFile $file = null
    ): ?Project {
        try {
            DB::transaction(function () use ($project, $formData, $file) {
                // if file exists
                if ($file) {
                    // if project contains a file, delete from storage
                    if ($project->document_path) {
                        Storage::disk('public')->delete($project->document_path);
                    }

                    $filePath = $file->store('documents', 'public');
                    $formData['document_path'] = $filePath;
                }

                // send notification
                $owner = $project->owner;
                $members = $project->members()->get();

                foreach ($members as $member) {
                    if ($member->id !== $owner->id) {
                        $this->notificationService->projectUpdated(
                            receiver: $member,
                            project: $project,
                            sender: $owner
                        );
                    }
                }

                // update project
                $project->update($formData);
            });

            return $project->fresh();
        } catch (\Throwable $th) {
            Log::error('Project update failed', [
                'error' => $th->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Delete project
     */
    public function deleteProject(Project $project): bool
    {
        try {
            DB::transaction(function () use ($project) {
                // if file, delete from storage
                if ($project->document_path) {
                    Storage::disk('public')->delete($project->document_path);
                }

                // send notification
                $owner = $project->owner;
                $members = $project->members()->get();

                foreach ($members as $member) {
                    if ($member->id !== $owner->id) {
                        $this->notificationService->projectDeleted(
                            receiver: $member,
                            project: $project,
                            sender: $owner
                        );
                    }
                }

                // delete from db 
                $project->delete();
            });

            return true;
        } catch (\Throwable $th) {
            Log::error('Project deletion failed', [
                'error' => $th->getMessage()
            ]);

            return false;
        }
    }
}
