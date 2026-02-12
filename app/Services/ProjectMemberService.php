<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Exceptions\ProjectMemberException;
use App\Services\Mail\MailService;
use App\Enums\NotificationType;
use App\Enums\UserRole;
use App\Models\Project;
use App\Models\User;
use App\Models\Notification;

class ProjectMemberService
{
    public function __construct(
        protected readonly NotificationService $notificationService,
        protected readonly MailService $mailService,
    ) {}

    public const MAX_MEMBERS_PER_PROJECT = 5;

    /**
     * Get available users to invite to project
     */
    public function getAvailableUsers(Project $project): Collection
    {
        $existingMembers = $project->members()->pluck('users.id');

        $pendingInvitationUserIds = Notification::where('notifiable_type', Project::class)
            ->where('notifiable_id', $project->id)
            ->where('type', NotificationType::INVITATION)
            ->whereNull('action_taken')
            ->pluck('user_id');

        $query = User::query()
            ->where('role', '!=', UserRole::ADMIN)
            ->where('id', '!=', $project->owner_id)
            ->whereNotIn('id', $existingMembers)
            ->whereNotIn('id', $pendingInvitationUserIds);

        return $query->get();
    }

    /**
     * Check if max members limit would be exceeded
     */
    public function checkMaxMembersLimit(
        Project $project,
        int $newMembersCount
    ): void {
        $currentCount = $project->members()->count();

        $pendingCount = Notification::where('notifiable_type', Project::class)
            ->where('notifiable_id', $project->id)
            ->where('type', NotificationType::INVITATION)
            ->whereNull('action_taken')
            ->count();

        if (($currentCount + $pendingCount + $newMembersCount) > self::MAX_MEMBERS_PER_PROJECT) {
            throw ProjectMemberException::maxMembersReached($project->id);
        }
    }

    /**
     * Check if user has pending invitation
     */
    private function hasPendingInvitation(
        Project $project,
        User $user
    ): bool {
        return Notification::where('notifiable_type', Project::class)
            ->where('user_id', $user->id)
            ->where('notifiable_id', $project->id)
            ->where('type', NotificationType::INVITATION)
            ->whereNull('action_taken')
            ->exists();
    }

    /**
     * Check user before inviting to project
     */
    private function checkUserBeforeInvite(
        Project $project,
        User $invitee
    ): void {
        if ($project->owner_id === $invitee->id) {
            throw ProjectMemberException::cannotInviteSelf(
                projectId: $project->id,
                userId: $project->owner_id
            );
        }

        if ($project->isMember($invitee)) {
            throw ProjectMemberException::alreadyMember(
                projectId: $project->id,
                userId: $invitee->id
            );
        }

        if ($this->hasPendingInvitation($project, $invitee)) {
            throw ProjectMemberException::alreadyInvited(
                projectId: $project->id,
                userId: $invitee->id
            );
        }
    }

    /**
     * Invite users to project
     */
    public function inviteMembers(
        Project $project,
        array $userIds,
        User $inviter
    ): void {
        try {
            DB::transaction(function () use ($project, $userIds, $inviter) {
                foreach ($userIds as $userId) {
                    $invitee = User::find($userId);

                    if ($invitee) {
                        $this->checkUserBeforeInvite($project, $invitee);
                        $this->mailService->sendInvitationEmail($invitee, $project, $inviter);
                        $this->notificationService->projectInvitation($invitee, $project, $inviter);
                    }
                }
            });
        } catch (ProjectMemberException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw ProjectMemberException::inviteUsersFailed(
                projectId: $project->id,
                previous: $e
            );
        }
    }

    /**
     * Check user before remove/leave from project
     */
    public function checkBeforeNoLongerMember(
        Project $project,
        User $member
    ): void {
        if ($project->owner_id === $member->id) {
            throw ProjectMemberException::projectOwner(
                projectId: $project->id,
                userId: $project->owner_id
            );
        }

        if (!$project->isMember($member)) {
            throw ProjectMemberException::notMember(
                projectId: $project->id,
                userId: $member->id
            );
        }
    }

    /**
     * Notify project members user has left
     */
    private function notifyMembersUserLeft(
        Project $project,
        User $formerMember
    ): void {
        $members = $project->members()->where('member_id', '!=', $formerMember->id)->get();

        foreach ($members as $member) {
            $this->notificationService->memberLeft(
                receiver: $member,
                project: $project,
                sender: $formerMember
            );
        }

        // $members = $project->members()->get();

        // foreach ($members as $member) {
        //     if ($member->id !== $formerMember->id) {
        //         $this->notificationService->memberLeft(
        //             receiver: $member,
        //             project: $project,
        //             sender: $formerMember
        //         );
        //     }
        // }
    }

    /**
     * Leave project
     */
    public function leaveProject(
        Project $project,
        User $user,
    ): void {
        try {
            DB::transaction(function () use ($project, $user) {
                $project->tasks()
                    ->where('assigned_to', $user->id)
                    ->update(['assigned_to' => null]);

                $project->members()->detach($user->id);

                $this->notifyMembersUserLeft(
                    project: $project,
                    formerMember: $user
                );
            });
        } catch (\Throwable $e) {
            throw ProjectMemberException::leaveProjectFailed(
                projectId: $project->id,
                userId: $user->id,
                previous: $e
            );
        }
    }

    /**
     * Remove member from project
     */
    public function removeMember(
        Project $project,
        User $member,
    ): void {
        try {
            DB::transaction(function () use ($project, $member) {
                $project->tasks()
                    ->where('assigned_to', $member->id)
                    ->update(['assigned_to' => null]);

                $project->members()->detach($member->id);

                $this->notificationService->removedFromProject(
                    receiver: $member,
                    project: $project,
                    sender: $project->owner
                );
            });
        } catch (\Throwable $e) {
            throw ProjectMemberException::removeMemberFailed(
                projectId: $project->id,
                userId: $member->id,
                previous: $e
            );
        }
    }
}
