<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProjectInvitationMail;
use App\Models\Project;
use App\Models\User;

class ProjectMemberService
{
    /**
     * Get available users to invite to project
     */
    public function getAvailableUsers(Project $project): Collection
    {
        $existingMembers = $project->members()->pluck('users.id');

        $query = User::query()
            ->where('role', '!=', 'admin')
            ->where('id', '!=', $project->owner_id)
            ->whereNotIn('id', $existingMembers);

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
                    // add user to project
                    $project->members()->attach($userId, [
                        'joined_at' => now(),
                    ]);

                    // get user for email notification
                    $user = User::find($userId);

                    if ($user) {
                        $this->sendInvitationEmail($user, $project, $inviter);
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
        User $member
    ): bool {
        // if owner
        if ($project->isOwner($member)) {
            return false;
        }

        // run remove user
        try {
            $project->members()->detach($member->id);

            return true;
        } catch (\Throwable $th) {
            Log::error('Failed to send project invitation email', [
                'user_id' => $member->id,
                'project_id' => $project->id,
                'error' => $th->getMessage(),
            ]);

            return false;
        }
    }
}
