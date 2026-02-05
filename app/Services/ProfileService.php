<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use App\Exceptions\ProfileException;
use App\Models\User;

class ProfileService
{
    public function __construct(protected readonly AvatarService $avatarService) {}

    /**
     * Validate user password
     */
    public function validateUserPassword(
        User $user,
        string $oldPassword
    ): void {
        if (!Hash::check($oldPassword, $user->password)) {
            throw ProfileException::passwordIncorrect($user->id);
        };
    }

    /**
     * Change user password
     */
    public function changePassword(
        User $user,
        string $newPassword
    ): void {
        try {
            $user->update([
                'password' => $newPassword,
            ]);
        } catch (\Throwable $e) {
            throw ProfileException::passwordChangeFailed($user->id, $e);
        }
    }

    /**
     * Delete user account
     */
    public function deleteAccount(User $user): void
    {
        try {
            $user->assignedTasks()->update(['assigned_to' => null]);

            if($user->avatar){
                $this->avatarService->deleteAvatarDirectory($user);
            }

            $user->delete();
        } catch (\Throwable $e) {
            throw ProfileException::accountDeleteFailed($user->id, $e);
        }
    }
}
