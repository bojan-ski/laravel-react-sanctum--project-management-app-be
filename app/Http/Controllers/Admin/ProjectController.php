<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SearchProjectsRequest;
use App\Http\Resources\ProjectCardResource;
use App\Services\Admin\ProjectService;
use App\Traits\ApiResponse;

class ProjectController extends Controller
{
    use ApiResponse;

    public function __construct(protected readonly ProjectService $projectService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(SearchProjectsRequest $request): JsonResponse
    {
        $projects = $this->projectService->getAllProjects(
            search: $request->validated('search') ?? null,
            perPage: 12
        );

        $projects->setCollection(
            ProjectCardResource::collection($projects)->collection
        );

        return $this->success(
            message: 'All projects retrieved',
            data: $projects
        );
    }

    /**
     * Get app project statistics.
     */
    public function stats(): JsonResponse
    {
        $stats = $this->projectService->getStats();

        return $this->success(
            message: 'Project statistics retrieved',
            data: $stats,
        );
    }
}
