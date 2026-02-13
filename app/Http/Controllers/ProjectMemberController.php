<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Project\InviteMembersRequest;
use App\Http\Resources\UserResource;
use App\Exceptions\ProjectMemberException;
use App\Exceptions\NotificationException;
use App\Services\ProjectMemberService;
use App\Traits\ApiResponse;
use App\Models\Project;
use App\Models\User;

class ProjectMemberController extends Controller
{
    use ApiResponse;

    public function __construct(protected readonly ProjectMemberService $memberService) {}

    /**
     * Get list of available users
     */
    public function availableUsers(Project $project): JsonResponse
    {
        $users = $this->memberService->getAvailableUsers($project);

        return $this->success(
            message: 'Available users retrieved',
            data: UserResource::collection($users)
        );
    }

    /**
     * Invite multiple members to project
     */
    public function invite(
        InviteMembersRequest $request,
        Project $project
    ): JsonResponse {
        $this->memberService->checkMaxMembersLimit(
            project: $project,
            newMembersCount: count($request->user_ids)
        );

        try {
            $this->memberService->inviteMembers(
                project: $project,
                userIds: $request->user_ids,
                inviter: $request->user()
            );

            $message = count($request->user_ids) . ' member(s) invited';

            return $this->success(
                message: $message,
                data: [
                    'user_ids' => $request->user_ids
                ]
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
        }
    }

    /**
     * Leave project
     */
    public function leave(
        Request $request,
        Project $project
    ): JsonResponse {
        $user = $request->user();

        $this->memberService->checkBeforeNoLongerMember(
            project: $project,
            member: $user
        );

        try {
            $this->memberService->leaveProject(
                $project,
                $user
            );

            $message = 'You left project - ' . $project->title;

            return $this->success(
                message: $message,
                data: [
                    'project_id' => $project->id
                ]
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
        }
    }

    /**
     * Remove member from project
     */
    public function remove(
        Project $project,
        User $member
    ): JsonResponse {
        $this->memberService->checkBeforeNoLongerMember(
            $project,
            $member
        );

        try {
            $this->memberService->removeMember(
                $project,
                $member
            );

            $message = $member->name . ' removed from project';

            return $this->success(
                message: $message,
                data: [
                    'member_id' => $member->id
                ]
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
        }
    }
}
