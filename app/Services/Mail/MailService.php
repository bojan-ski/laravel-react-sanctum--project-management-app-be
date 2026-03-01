<?php

namespace App\Services\Mail;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\ProjectInvitationMail;
use App\Mail\UserCredentialsMail;
use App\Models\Project;
use App\Models\User;

class MailService
{
    /**
     * Send invitation email to user
     */
    public function sendCredentialsEmail(
        User $user,
        string $password
    ): void {
        try {
            Mail::to($user->email)->send(
                new UserCredentialsMail($user, $password)
            );
        } catch (\Throwable $th) {
            Log::error('Failed to send user credentials email', [
                'user_id' => $user->id,
                'error' => $th->getMessage(),
            ]);
        }
    }

    /**
     * Send project invitation email to user
     */
    public function sendProjectInvitationEmail(
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
}
