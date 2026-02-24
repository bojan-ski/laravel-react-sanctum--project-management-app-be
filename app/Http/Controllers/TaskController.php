<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Requests\Task\FilterUserTasksRequest;
use App\Http\Requests\Task\CreateTaskRequest;
use App\Http\Requests\Task\UpdateTaskPriorityRequest;
use App\Http\Requests\Task\UpdateTaskStatusRequest;
use App\Http\Requests\Task\AssigneeDocumentRequest;
use App\Http\Resources\TaskResource;
use App\Exceptions\TaskException;
use App\Exceptions\NotificationException;
use App\Exceptions\ProjectMemberException;
use App\Exceptions\TaskActivityException;
use App\Exceptions\DocumentException;
use App\Services\ProjectMemberService;
use App\Services\TaskService;
use App\Traits\ApiResponse;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;

class TaskController extends Controller
{
    use ApiResponse;

    public function __construct(
        protected readonly ProjectMemberService $memberService,
        protected readonly TaskService $taskService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(FilterUserTasksRequest $request): JsonResponse
    {
        $filters = $request->validated();

        $ownership = $filters['ownership'] ?? 'all';
        $status = $filters['status'] ?? 'all';
        $priority = $filters['priority'] ?? 'all';

        $tasks = $this->taskService->getUserTasks(
            user: $request->user(),
            ownership: $ownership,
            status: $status,
            priority: $priority,
            perPage: 12
        );

        $tasks->setCollection(
            TaskResource::collection($tasks)->collection
        );

        return $this->success(
            message: 'User tasks retrieved',
            data: $tasks
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        CreateTaskRequest $request,
        Project $project
    ): JsonResponse {
        $this->memberService->checkMemberStatus(
            project: $project,
            member: User::find($request['assigned_to'])
        );

        try {
            $newTask = $this->taskService->createTask(
                project: $project,
                projectOwner: $request->user(),
                formData: $request->validated()
            );

            return $this->success(
                message: 'Task created',
                data: new TaskResource($newTask),
            );
        } catch (ProjectMemberException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: $e->getStatusCode()
            );
        } catch (NotificationException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: $e->getStatusCode()
            );
        } catch (TaskException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: $e->getStatusCode()
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Task $task)
    {
        $taskDetails = $this->taskService->getTaskDetails($task);

        return $this->success(
            message: 'Task retrieved successfully',
            data: new TaskResource($taskDetails),
        );
    }

    /**
     * Update task status
     */
    public function updateStatus(
        UpdateTaskStatusRequest $request,
        Task $task
    ): JsonResponse {
        try {
            $updatedTask = $this->taskService->statusChange(
                task: $task,
                newStatus: $request->validated('status'),
            );

            return $this->success(
                message: 'Task status updated',
                data: [
                    'id' => $task->id,
                    'status' => $updatedTask->status
                ]
            );
        } catch (TaskException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: $e->getStatusCode()
            );
        } catch (TaskActivityException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: $e->getStatusCode()
            );
        } catch (NotificationException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: $e->getStatusCode()
            );
        }
    }

    /**
     * Update task priority
     */
    public function updatePriority(
        UpdateTaskPriorityRequest $request,
        Task $task
    ): JsonResponse {
        try {
            $updatedTask = $this->taskService->priorityChange(
                task: $task,
                newPriority: $request->validated('priority'),
            );

            return $this->success(
                message: 'Task priority updated',
                data: [
                    'id' => $task->id,
                    'priority' => $updatedTask->priority
                ]
            );
        } catch (TaskException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: $e->getStatusCode()
            );
        } catch (TaskActivityException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: $e->getStatusCode()
            );
        } catch (NotificationException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: $e->getStatusCode()
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Task $task): JsonResponse
    {
        try {
            $this->taskService->deleteTask($task);

            return $this->success(message: 'Task deleted');
        } catch (DocumentException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: $e->getStatusCode()
            );
        } catch (NotificationException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: $e->getStatusCode()
            );
        } catch (TaskException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: $e->getStatusCode()
            );
        }
    }

    /**
     * Upload task/assignee document.
     */
    public function uploadTaskDocument(
        AssigneeDocumentRequest $request,
        Task $task
    ): JsonResponse {
        try {
            $this->taskService->uploadTaskDocument(
                uploader: $request->user(),
                task: $task,
                file: $request->validated('document')
            );

            return $this->success(message: 'Document uploaded');
        } catch (TaskActivityException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: $e->getStatusCode()
            );
        } catch (DocumentException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: $e->getStatusCode()
            );
        }
    }
}
