<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class ProfileService
{
    public function __construct(private AvatarService $avatarService) {}

    /**
     * Upload user avatar
     */
    public function uploadAvatar(
        User $user,
        UploadedFile $avatar
    ): ?User {
        try {
            // delete old avatar
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }

            $path = $this->avatarService->processAvatar($avatar, $user->id);

            $user->avatar = $path;
            $user->save();

            return $user;
        } catch (\Throwable $th) {
            Log::error('Avatar upload failed', [
                'error' => $th->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Change user password
     */
    public function changePassword(
        User $user,
        string $newPassword
    ): bool {
        try {
            $user->password = $newPassword;
            $user->save();

            return true;
        } catch (\Throwable $th) {
            Log::error('Change password failed', [
                'error' => $th->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Delete user account
     */
    public function deleteAccount(
        User $user,
    ): bool {
        try {
            Auth::guard('web')->logout();

            session()->invalidate();
            session()->regenerateToken();

            $user->delete();

            return true;
        } catch (\Throwable $th) {
            Log::error('Delete account failed', [
                'error' => $th->getMessage(),
            ]);

            return false;
        }
    }
}
