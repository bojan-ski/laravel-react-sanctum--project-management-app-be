<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Exceptions\ProjectException;
use App\Models\User;
use App\Models\Project;

class ProjectService
{
    public function __construct(
        protected readonly NotificationService $notificationService,
        protected readonly DocumentService $documentService,
    ) {}

    /**
     * Get user projects with filter feature
     */
    public function getUserProjects(
        User $user,
        string $ownership = 'all',
        string $status = 'all',
        int $perPage = 12
    ): LengthAwarePaginator {
        $query = Project::query();

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

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        return $query->with(['owner'])
            ->withCount(['members'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create project
     */
    public function createProject(
        User $user,
        array $formData,
        ?UploadedFile $file = null
    ): void {
        try {
            DB::transaction(function () use ($user, $formData, $file) {
                if ($file) {
                    $filePath = $file->store('documents', 'public');
                    $formData['document_path'] = $filePath;
                }

                $project = Project::create([
                    'title' => $formData['title'],
                    'description' => $formData['description'],
                    'deadline' => $formData['deadline'],
                    'document_path' => $formData['document_path'] ?? null
                ]);

                $project->members()->attach($user->id, [
                    'joined_at' => now(),
                ]);
            });
        } catch (\Throwable $e) {
            throw ProjectException::createProjectFailed($user->id, $e);
        }
    }

    /**
     * Get selected project details
     */
    public function getProjectDetails(Project $project): Project
    {
        return $project->load([
            'owner:id,name,email,avatar',
            'members:id,name,email,avatar',
        ])->loadCount(['members', 'tasks']);
    }

    /**
     * Notify project members of update
     */
    private function notifyMembersOfUpdate(
        Project $project,
        User $owner
    ): void {
        $members = $project->members()->where('member_id', '!=', $owner->id)->get();

        foreach ($members as $member) {
            $this->notificationService->projectUpdated(
                receiver: $member,
                project: $project,
                sender: $owner
            );
        }

        // $members = $project->members()->get();

        // foreach ($members as $member) {
        //     if ($member->id !== $owner->id) {
        //         $this->notificationService->projectUpdated(
        //             receiver: $member,
        //             project: $project,
        //             sender: $owner
        //         );
        //     }
        // }
    }

    /**
     * Update project
     */
    public function updateProject(
        Project $project,
        array $formData,
        ?UploadedFile $file = null
    ): Project {
        try {
            DB::transaction(function () use ($project, $formData, $file) {
                if ($file) {
                    if ($project->document_path) {
                        Storage::disk('public')->delete($project->document_path);
                    }

                    $filePath = $file->store('documents', 'public');
                    $formData['document_path'] = $filePath;
                }

                $project->update($formData);

                $this->notifyMembersOfUpdate($project, $project->owner);
            });

            return $project->fresh(['owner', 'members']);
        } catch (\Throwable $e) {
            throw ProjectException::updateProjectFailed($project->id, $e);
        }
    }

    /**
     * Change project status
     */
    public function statusChange(
        Project $project,
        string $status,
    ): ?Project {
        try {
            $project->update([
                'status' => $status
            ]);

            $this->notifyMembersOfUpdate($project, $project->owner);

            return $project->fresh(['owner', 'members']);
        } catch (\Throwable $e) {
            throw ProjectException::changeProjectStatusFailed($project->id);
        }
    }

    /**
     * Notify project members of project deletion
     */
    private function notifyMembersOfDeletion(
        Project $project,
        User $owner
    ): void {
        $members = $project->members()->where('member_id', '!=', $owner->id)->get();

        foreach ($members as $member) {
            $this->notificationService->projectDeleted(
                receiver: $member,
                project: $project,
                sender: $owner
            );
        }

        // $members = $project->members()->get();

        // foreach ($members as $member) {
        //     if ($member->id !== $owner->id) {
        //         $this->notificationService->projectDeleted(
        //             receiver: $member,
        //             project: $project,
        //             sender: $owner
        //         );
        //     }
        // }
    }

    /**
     * Delete project
     */
    public function deleteProject(Project $project): void
    {
        try {
            DB::transaction(function () use ($project) {
                if ($project->document_path) {
                    Storage::disk('public')->delete($project->document_path);
                }

                $this->notifyMembersOfDeletion($project, $project->owner);

                $project->delete();
            });
        } catch (\Throwable $e) {
            throw ProjectException::deleteProjectFailed($project->id, $e);
        }
    }
}
