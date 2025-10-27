<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Project\FilterRequest;
use App\Http\Requests\Project\ProjectRequest;
use App\Http\Resources\ProjectCardResource;
use App\Services\ProjectService;
use App\Traits\ApiResponse;

class ProjectController extends Controller
{
    use ApiResponse;

    public function __construct(private ProjectService $projectService) {}

    /**
     * Display a listing of user's projects
     */
    public function index(FilterRequest $request): JsonResponse
    {
        // filter options
        $ownership = $request->input('ownership', $request->validated());

        // get projects
        $projects = $this->projectService->getUserProjects(
            $request->user(),
            $ownership
        );

        // structure json result
        $projects->setCollection(
            ProjectCardResource::collection($projects)->collection
        );

        // return json
        return $this->success($projects, 'Projects retrieved');
    }

    /**
     * Store a newly created project
     */
    public function store(ProjectRequest $request): JsonResponse
    {
        $response = $this->projectService->createProject(
            $request->user(),
            $request->validated()
        );

        if (!$response) {
            return $this->error('Failed to create project!', 500);
        }

        return $this->success(null, 'Project created', 201);
    }
}
