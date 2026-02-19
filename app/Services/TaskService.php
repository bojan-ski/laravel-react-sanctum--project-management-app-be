<?php

namespace App\Services;

use App\Exceptions\TaskException;
use App\Exceptions\TaskActivityException;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskActivity;

class TaskService
{
    public function __construct(protected readonly NotificationService $notificationService) {}

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
                'priority' => $formData['priority'],
                'due_date' => $formData['due_date'],
            ]);

            $this->notificationService->taskAssigned(
                receiver: $task->assignee,
                project: $project,
                task: $task,
                sender: $projectOwner
            );

            return $task;
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
            'creator',
            'assignee',
            'project.owner',
            'activities.user',
        ]);
    }

    /**
     * Log task activity
     */
    private function logActivity(
        int $taskId,
        int $userId,
        string $action,
        array $changes
    ): void {
        try {
            TaskActivity::create([
                'task_id' => $taskId,
                'user_id' => $userId,
                'action' => $action,
                'changes' => $changes,
            ]);
        } catch (\Throwable $e) {
            throw TaskActivityException::logTaskActivityFailed($userId, $taskId, $action, $e);
        }
    }

    /**
     * Update task status
     */
    public function statusChange(
        Task $task,
        string $status
    ): Task {
        $oldStatus = $task->status;

        try {
            $task->update([
                'status' => $status
            ]);

            $this->logActivity($task->id, $task->created_by, 'status_changed', [
                'status' => ['from' => $oldStatus, 'to' => $status]
            ]);

            $this->notificationService->taskStatusChanged(
                receiver: $task->assignee,
                task: $task,
                sender: $task->creator
            );

            return $task->fresh();
        } catch (\Throwable $e) {
            throw TaskException::changeTaskStatusFailed($task->created_by, $task->id);
        }
    }

    /**
     * Update task priority
     */
    public function priorityChange(
        Task $task,
        string $priority
    ): Task {
        $oldPriority = $task->priority;

        try {
            $task->update([
                'priority' => $priority
            ]);

            $this->logActivity($task->id, $task->created_by, 'priority_changed', [
                'priority' => ['from' => $oldPriority, 'to' => $priority]
            ]);

            $this->notificationService->taskPriorityChanged(
                receiver: $task->assignee,
                task: $task,
                sender: $task->creator
            );

            return $task->fresh();
        } catch (\Throwable $e) {
            throw TaskException::changeTaskPriorityFailed($task->created_by, $task->id);
        }
    }

    /**
     * Delete task
     */
    public function deleteTask(Task $task): void {
        try {
            $task->delete();
        } catch (\Throwable $e) {
            throw TaskException::deleteTaskFailed($task->id, $e);
        }

        $this->logActivity($task->id, $task->created_by, 'deleted', [
            'title' => $task->title,
        ]);

        $this->notificationService->taskDeleted(
            receiver: $task->assignee,
            task: $task,
            sender: $task->creator
        );
    }
}
