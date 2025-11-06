<?php

namespace App\Http\Controllers;

use App\Http\Requests\Project\InviteMembersRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Resources\UserResource;
use App\Services\ProjectMemberService;
use App\Models\Project;
use App\Traits\ApiResponse;

class ProjectMemberController extends Controller
{
    use ApiResponse;

    public function __construct(private ProjectMemberService $memberService) {}

    /**
     * Get list of available users
     */
    public function availableUsers(Project $project): JsonResponse
    {
        $users = $this->memberService->getAvailableUsers($project);

        return $this->success(
            UserResource::collection($users),
            'Available users retrieved'
        );
    }

    /**
     * Invite multiple members to project
     */
    public function invite(InviteMembersRequest $request, Project $project): JsonResponse
    {
        // call project member services
        $response = $this->memberService->inviteMembers(
            $project,
            $request->user_ids,
            $request->user()
        );

        // return json
        if (!$response) {
            return $this->error('Failed to create project!', 500);
        }

        $message = count($request->user_ids) . ' member(s) invited';

        return $this->success(null, $message);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
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
