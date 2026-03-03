<?php

namespace App\Services;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\UploadedFile;
use App\Exceptions\TaskException;
use App\Exceptions\DocumentException;
use App\Exceptions\NotificationException;
use App\Enums\TaskStatus;
use App\Enums\TaskActivityAction;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;

class TaskService
{
    public function __construct(
        protected readonly NotificationCreationService $notificationCreationService,
        protected readonly DocumentService $documentService,
        protected readonly TaskActivityService $taskActivityService,
    ) {}

    /**
     * Get user's tasks with filters (created by or assigned to)
     */
    public function getUserTasks(
        User $user,
        string $ownership = 'all',
        string $status = 'all',
        string $priority = 'all',
        int $perPage = 12
    ): LengthAwarePaginator {
        $query = Task::query();

        switch ($ownership) {
            case 'created':
                $query->where('created_by', $user->id);
                break;

            case 'assigned':
                $query->where('assigned_to', $user->id);
                break;

            case 'all':
            default:
                $query->where(function ($q) use ($user) {
                    $q->where('created_by', $user->id)
                        ->orWhere('assigned_to', $user->id);
                });
                break;
        }

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        if ($priority !== 'all') {
            $query->where('priority', $priority);
        }

        return $query->with(['project', 'assignee', 'activities'])
            ->withCount('activities')
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a new task
     */
    public function createTask(
        Project $project,
        User $projectOwner,
        array $formData
    ): Task {
        try {
            $task = Task::create([
                'project_id' => $project->id,
                'created_by' => $projectOwner->id,
                'assigned_to' => $formData['assigned_to'],
                'title' => $formData['title'],
                'description' => $formData['description'],
                'status' => TaskStatus::TO_DO,
                'priority' => $formData['priority'],
                'due_date' => $formData['due_date'],
            ]);

            $this->notificationCreationService->taskAssigned(
                receiver: $task->assignee,
                project: $project,
                task: $task,
                sender: $projectOwner
            );

            return $task->load(['project', 'creator', 'assignee']);
        } catch (NotificationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw TaskException::createTaskFailed($projectOwner->id, $project->id);
        }
    }

    /**
     * Get selected task details
     */
    public function getTaskDetails(Task $task): Task
    {
        return $task->load([
            'project',
            'creator',
            'assignee',
            'activities.user',
            'messages.user',
        ]);
    }

    /**
     * Update task status
     */
    public function statusChange(
        Task $task,
        string $newStatus
    ): Task {
        $oldStatus = $task->status->value;

        try {
            $task->update([
                'status' => $newStatus
            ]);
        } catch (\Throwable $e) {
            throw TaskException::changeTaskStatusFailed($task->created_by, $task->id);
        }

        $this->taskActivityService->logTaskActivity(
            taskId: $task->id,
            userId: $task->created_by,
            action: TaskActivityAction::STATUS_CHANGED,
            changes: ['from' => $oldStatus, 'to' => $newStatus]
        );

        $this->notificationCreationService->taskStatusChanged(
            receiver: $task->assignee,
            task: $task,
            sender: $task->creator
        );

        return $task->fresh();
    }

    /**
     * Update task priority
     */
    public function priorityChange(
        Task $task,
        string $newPriority
    ): Task {
        $oldPriority = $task->priority->value;

        try {
            $task->update([
                'priority' => $newPriority
            ]);
        } catch (\Throwable $e) {
            throw TaskException::changeTaskPriorityFailed($task->created_by, $task->id);
        }

        $this->taskActivityService->logTaskActivity(
            taskId: $task->id,
            userId: $task->created_by,
            action: TaskActivityAction::PRIORITY_CHANGED,
            changes: ['from' => $oldPriority, 'to' => $newPriority]
        );

        $this->notificationCreationService->taskPriorityChanged(
            receiver: $task->assignee,
            task: $task,
            sender: $task->creator
        );

        return $task->fresh();
    }

    /**
     * Delete task
     */
    public function deleteTask(Task $task): void
    {
        try {
            DB::transaction(function () use ($task) {
                $this->documentService->deleteDocumentDirectory($task);

                $this->notificationCreationService->taskDeleted(
                    receiver: $task->assignee,
                    task: $task,
                    sender: $task->creator
                );

                $task->delete();
            });
        } catch (DocumentException $e) {
            throw $e;
        } catch (NotificationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw TaskException::deleteTaskFailed($task->id, $e);
        }
    }

    /**
     * Upload task/assignee document
     */
    public function uploadTaskDocument(
        User $uploader,
        Task $task,
        UploadedFile $file
    ): void {
        $activity = $this->taskActivityService->logTaskActivity(
            taskId: $task->id,
            userId: $uploader->id,
            action: TaskActivityAction::DOCUMENT_UPLOADED,
            changes: $file->getClientOriginalName() . ' uploaded'
        );

        $this->documentService->uploadDocument(
            uploader: $uploader,
            documentable: $activity,
            file: $file,
            storagePath: "documents/task/{$task->id}"
        );
    }
}
