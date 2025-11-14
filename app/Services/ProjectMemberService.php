<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProjectInvitationMail;
use App\Models\Notification;
use App\Models\Project;
use App\Models\User;

class ProjectMemberService
{
    public function __construct(private NotificationService $notificationService) {}

    /**
     * Get available users to invite to project
     */
    public function getAvailableUsers(Project $project): Collection
    {
        // get existing members
        $existingMembers = $project->members()->pluck('users.id');

        // get already invited users id who have not decided yet
        $pendingInvitationUserIds = Notification::where('notifiable_type', Project::class)
            ->where('notifiable_id', $project->id)
            ->where('type', 'invitation')
            ->whereNull('action_taken')
            ->pluck('user_id');

        // run query
        $query = User::query()
            ->where('role', '!=', 'admin')
            ->where('id', '!=', $project->owner_id)
            ->whereNotIn('id', $existingMembers)
            ->whereNotIn('id', $pendingInvitationUserIds);

        return $query->get();
    }

    /**
     * Invite users to project
     */
    public function inviteMembers(
        Project $project,
        array $userIds,
        User $inviter
    ): bool {
        try {
            DB::transaction(function () use ($project, $userIds, $inviter) {
                foreach ($userIds as $userId) {
                    $user = User::find($userId);

                    if ($user) {
                        // send email
                        $this->sendInvitationEmail($user, $project, $inviter);

                        // send notification
                        $this->notificationService->createInvitation($user, $project, $inviter);
                    }
                }
            });

            return true;
        } catch (\Throwable $th) {
            Log::error('Invite members failed', [
                'error' => $th->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Send invitation email to user
     */
    private function sendInvitationEmail(
        User $user,
        Project $project,
        User $inviter
    ): void {
        try {
            Mail::to($user->email)->queue(
                new ProjectInvitationMail($user, $project, $inviter)
            );
        } catch (\Throwable $th) {
            Log::error('Failed to send project invitation email', [
                'user_id' => $user->id,
                'project_id' => $project->id,
                'error' => $th->getMessage(),
            ]);
        }
    }

    /**
     * Remove member from project
     */
    public function removeMember(
        Project $project,
        User $member,
        User $projectOwner
    ): bool {
        try {
            // run remove user
            $project->members()->detach($member->id);

            // send notification
            $this->notificationService->removeFromProject(
                $member, 
                $project, 
                $projectOwner
            );

            return true;
        } catch (\Throwable $th) {
            Log::error('Failed to remove project member', [
                'user_id' => $member->id,
                'project_id' => $project->id,
                'error' => $th->getMessage(),
            ]);

            return false;
        }
    }
}
