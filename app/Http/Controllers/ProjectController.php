<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Project\FilterRequest;
use App\Http\Requests\Project\ProjectRequest;
use App\Http\Requests\Project\StatusRequest;
use App\Http\Resources\ProjectCardResource;
use App\Http\Resources\ProjectResource;
use App\Services\ProjectService;
use App\Models\Project;
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
        $filters = $request->validated();

        $ownership = $filters['ownership'] ?? 'all';
        $status = $filters['status'] ?? 'all';

        $projects = $this->projectService->getUserProjects(
            $request->user(),
            $ownership,
            $status
        );

        $projects->setCollection(
            ProjectCardResource::collection($projects)->collection
        );

        return $this->success($projects, 'Projects retrieved');
    }

    /**
     * Store a newly created resource in storage.
     */
    // Run php artisan storage:link to create a symbolic link from public/storage to storage/app/public
    public function store(ProjectRequest $request): JsonResponse
    {
        $file = $request->file('document_path');

        $response = $this->projectService->createProject(
            $request->user(),
            $request->validated(),
            $file
        );

        if (!$response) {
            return $this->error('Failed to create project!', 500);
        }

        return $this->success(null, 'Project created', 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project): JsonResponse
    {
        $project = $this->projectService->getProjectDetails($project);

        return $this->success(
            new ProjectResource($project),
            'Project details retrieved'
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project): JsonResponse
    {
        return $this->success($project, 'Project data retrieved');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        ProjectRequest $request,
        Project $project
    ): JsonResponse {
        $file = $request->hasFile('document_path') ? $request->file('document_path') : null;

        $updatedProject = $this->projectService->updateProject(
            $project,
            $request->validated(),
            $file
        );

        if (!$updatedProject) {
            return $this->error('Failed to update project!', 500);
        }

        return $this->success($updatedProject, 'Project updated', 201);
    }

    /**
     * Change project status to completed or cancelled
     */
    public function status(
        StatusRequest $request,
        Project $project
    ): JsonResponse {
        $status = $request->validated()['status'];

        $updatedProject = $this->projectService->statusChange(
            $status,
            $project,
        );

        if (!$updatedProject) {
            return $this->error('Failed to change project status!', 500);
        }

        return $this->success($updatedProject, 'Project status updated', 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project): JsonResponse
    {
        $response = $this->projectService->deleteProject($project);

        if (!$response) {
            return $this->error('Delete project error!', 500);
        }

        return $this->success(null, 'Project deleted');
    }
}
