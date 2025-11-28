<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskService
{
    public function __construct(private NotificationService $notificationService) {}

    /**
     * Create a new task
     */
    public function createTask(
        Project $project,
        User $projectOwner,
        array $formData
    ): Task | bool {
        try {
            DB::transaction(function () use ($project, $projectOwner, $formData) {
                // create task
                $task = Task::create([
                    'project_id' => $project->id,
                    'created_by' => $projectOwner->id,
                    'assigned_to' => $formData['assigned_to'],
                    'title' => $formData['title'],
                    'description' => $formData['description'],
                    'priority' => $formData['priority'] ?? 'low',
                    'due_date' => $formData['due_date'],
                ]);

                // send notification            
                $this->notificationService->taskAssigned(
                    $task->assignee,
                    $project,
                    $task,
                    $projectOwner
                );
            });

            return true;
        } catch (\Throwable $th) {
            Log::error('Task created failed', [
                'error' => $th->getMessage()
            ]);

            return false;
        }
    }
}
