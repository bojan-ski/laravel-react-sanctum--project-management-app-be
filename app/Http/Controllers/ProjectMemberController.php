<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Project\InviteMembersRequest;
use App\Http\Resources\UserResource;
use App\Services\ProjectMemberService;
use App\Models\Project;
use App\Models\User;
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
    public function invite(
        InviteMembersRequest $request,
        Project $project
    ): JsonResponse {
        $response = $this->memberService->inviteMembers(
            $project,
            $request->user_ids,
            $request->user()
        );

        if (!$response) {
            return $this->error('Failed to invite members!', 500);
        }

        $message = count($request->user_ids) . ' member(s) invited';
        return $this->success(null, $message);
    }

    /**
     * Remove member from project
     */
    public function remove(
        Project $project,
        User $member
    ): JsonResponse {
        if (!$project->isMember($member)) {
            return $this->error('Member does not exist', 404);
        }

        $projectOwner = auth()->user();

        $response = $this->memberService->removeMember(
            $project,
            $member,
            $projectOwner
        );

        if (!$response) {
            return $this->error('Failed to remove member', 500);
        }

        $message = $member->name . ' removed from project';
        return $this->success(null, $message);
    }
}
