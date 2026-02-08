<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Requests\Project\FilterProjectsRequest;
use App\Http\Requests\Project\ProjectRequest;
use App\Http\Requests\Project\UpdateProjectStatusRequest;
use App\Http\Resources\ProjectCardResource;
use App\Http\Resources\ProjectResource;
use App\Exceptions\ProjectException;
use App\Exceptions\DocumentException;
use App\Services\ProjectService;
use App\Traits\ApiResponse;
use App\Models\Project;

class ProjectController extends Controller
{
    use ApiResponse;

    public function __construct(protected readonly ProjectService $projectService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(FilterProjectsRequest $request): JsonResponse
    {
        $filters = $request->validated();

        $ownership = $filters['ownership'] ?? 'all';
        $status = $filters['status'] ?? 'all';

        $projects = $this->projectService->getUserProjects(
            user: $request->user(),
            ownership: $ownership,
            status: $status,
            perPage: 12
        );

        $projects->setCollection(
            ProjectCardResource::collection($projects)->collection
        );

        return $this->success(
            message: 'Projects retrieved',
            data: $projects
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    // Run php artisan storage:link to create a symbolic link from public/storage to storage/app/public
    public function store(ProjectRequest $request): JsonResponse
    {
        try {
            $this->projectService->createProject(
                user: $request->user(),
                formData: $request->validated(),
                file: $request->file('document_path') ?? null
            );

            return $this->success(message: 'Project created');
        } catch (ProjectException $e) {
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

    /**
     * Display the specified resource.
     */
    public function show(Project $project): JsonResponse
    {
        $project = $this->projectService->getProjectDetails($project);

        return $this->success(
            message: 'Project details retrieved',
            data: new ProjectResource($project)
        );
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Project $project): JsonResponse
    {
        return $this->success(
            message: 'Project data retrieved',
            data: $project
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(
        ProjectRequest $request,
        Project $project
    ): JsonResponse {
        try {
            $updatedProject = $this->projectService->updateProject(
                project: $project,
                formData: $request->validated(),
                file: $request->file('document_path') ?? null
            );

            return $this->success(
                message: 'Project updated',
                data: new ProjectResource($updatedProject)
            );
        } catch (ProjectException $e) {
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

    /**
     * Change project status to completed or cancelled
     */
    public function status(
        UpdateProjectStatusRequest $request,
        Project $project
    ): JsonResponse {
        try {
            $updatedProject = $this->projectService->statusChange(
                project: $project,
                status: $request->validated('status'),
            );

            return $this->success(
                message: 'Project status updated',
                data: new ProjectResource($updatedProject)
            );
        } catch (ProjectException $e) {
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
    public function destroy(Project $project): JsonResponse
    {
        try {
            $this->projectService->deleteProject($project);

            return $this->success(message: 'Project deleted');
        } catch (ProjectException $e) {
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
