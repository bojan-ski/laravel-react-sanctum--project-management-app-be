<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use App\Exceptions\NotificationException;
use App\Enums\InvitationStatus;
use App\Models\Notification;
use App\Models\User;

class InvitationService
{
    /**
     * Validate notification
     */
    public function validateInvitation(Notification $notification): void
    {
        if (!$notification->isInvitation()) {
            throw NotificationException::notAnInvitation($notification->id);
        }

        if (!$notification->isPending()) {
            throw NotificationException::alreadyResponded($notification->id);
        }

        $project = $notification->notifiable;

        if (!$project) {
            throw NotificationException::projectNotFound($notification->id);
        }
    }

    /**
     * Accept project invitation
     */
    public function acceptInvitation(
        Notification $notification,
        User $user
    ): Notification {
        try {
            $project = $notification->notifiable;

            DB::transaction(function () use ($notification, $project, $user) {
                $project->members()->attach($user->id, [
                    'joined_at' => now(),
                ]);

                $notification->update([
                    'action_taken' => InvitationStatus::ACCEPTED,
                    'read_at' => now(),
                ]);
            });

            return $notification->fresh();
        } catch (\Throwable $e) {
            throw NotificationException::acceptInvitationFailed(
                notificationId: $notification->id,
                userId: $user->id,
            );
        }
    }

    /**
     * Decline project invitation
     */
    public function declineInvitation(
        Notification $notification,
        User $user
    ): Notification {
        try {
            $notification->update([
                'action_taken' => InvitationStatus::DECLINED,
                'read_at' => now(),
            ]);

            return $notification->fresh();
        } catch (\Throwable $e) {
            throw NotificationException::declineInvitationFailed(
                notificationId: $notification->id,
                userId: $user->id,
            );
        }
    }
}
