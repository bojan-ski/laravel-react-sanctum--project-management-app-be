<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Project\ProjectRequest;
use App\Services\ProjectService;
use App\Traits\ApiResponse;

class ProjectController extends Controller
{
    use ApiResponse;

    public function __construct(private ProjectService $projectService) {}

    /**
     * Store a newly created project
     */
    public function store(ProjectRequest $request): JsonResponse
    {
        $project = $this->projectService->createProject(
            $request->user(),
            $request->validated()
        );

        if ($project) {
            return $this->success(null, 'Project created', 201);
        }

        return $this->error('Failed to create project - test.', 500);
    }
}
