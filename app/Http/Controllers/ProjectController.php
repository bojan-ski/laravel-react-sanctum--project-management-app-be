<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Project\FilterRequest;
use App\Http\Requests\Project\ProjectRequest;
use App\Http\Resources\ProjectCardResource;
use App\Models\Project;
use App\Services\ProjectService;
use App\Traits\ApiResponse;

class ProjectController extends Controller
{
    use ApiResponse;

    public function __construct(private ProjectService $projectService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(FilterRequest $request): JsonResponse
    {
        // filter options
        $ownership = $request->input('ownership', $request->validated());
        $status = $request->input('status');

        // get projects
        $projects = $this->projectService->getUserProjects(
            $request->user(),
            $ownership,
            $status
        );

        // structure json result
        $projects->setCollection(
            ProjectCardResource::collection($projects)->collection
        );

        // return json
        return $this->success($projects, 'Projects retrieved');
    }

    /**
     * Store a newly created resource in storage.
     */
    // Run php artisan storage:link to create a symbolic link from public/storage to storage/app/public
    public function store(ProjectRequest $request): JsonResponse
    {
        // get file
        $file = $request->file('document_path');

        // call project service
        $response = $this->projectService->createProject(
            $request->user(),
            $request->validated(),
            $file
        );

        // return json
        if (!$response) {
            return $this->error('Failed to create project!', 500);
        }

        return $this->success(null, 'Project created', 201);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project): JsonResponse
    {
        return $this->success($project, 'Project retrieved');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProjectRequest $request, Project $project): JsonResponse
    {
        // get file
        $file = $request->hasFile('document_path') ? $request->file('document_path') : null;

        // call project service
        $updatedProject = $this->projectService->updateProject(
            $project,
            $request->validated(),
            $file
        );

        // return json
        if (!$updatedProject) {
            return $this->error('Failed to update project!', 500);
        }

        return $this->success($updatedProject, 'Project updated', 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project): JsonResponse
    {
        // call project service
        $response = $this->projectService->deleteProject($project);

        // return json
        if (!$response) {
            return $this->error('Delete project error!', 500);
        }

        return $this->success(null, 'Project deleted');
    }
}
