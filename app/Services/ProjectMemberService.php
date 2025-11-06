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
     * send invitation email to user
     */
    private function sendInvitationEmail(User $user, Project $project, User $inviter): void
    {
        try {
            Mail::to($user->email)->queue(
                new ProjectInvitationMail($user, $project, $inviter)
            );
        } catch (\Exception $e) {
            Log::error('Failed to send project invitation email', [
                'user_id' => $user->id,
                'project_id' => $project->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
