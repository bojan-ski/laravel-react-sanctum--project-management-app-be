<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class ProfileService
{
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
        Request $request,
        User $user,
    ): bool {
        try {
            Auth::guard('web')->logout();

            $request->session()->invalidate();

            $request->session()->regenerateToken();

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
