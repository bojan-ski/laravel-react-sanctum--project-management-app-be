<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Task\CreateTaskRequest;
use App\Services\TaskService;
use App\Models\Project;
use App\Models\User;
use App\Traits\ApiResponse;

class TaskController extends Controller
{
    use ApiResponse;

    public function __construct(private TaskService $taskService) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(
        CreateTaskRequest $request,
        Project $project
    ): JsonResponse {
        $assignee = User::find($request['assigned_to']);

        if ($assignee && !$project->isMember($assignee)) {
            return $this->error('Assigned user must be a project member!', 400);
        }

        // DELETE ON PROJECT COMPLETION
        if ($assignee && $project->isOwner($assignee)) {
            return $this->error('Can not assign task to project owner!', 400);
        }
        // DELETE ON PROJECT COMPLETION

        $response = $this->taskService->createTask(
            $project,
            $request->user(),
            $request->validated()
        );

        if (!$response) {
            return $this->error('Failed to create task!', 500);
        }

        return $this->success(null, 'Task created', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
