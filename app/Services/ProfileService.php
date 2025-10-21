<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ProfileService
{
    /**
     * change password
     */
    public function changePassword(User $user, string $oldPassword, string $newPassword): bool
    {
        if (!Hash::check($oldPassword, $user->password)) {
            return false;
        };

        $user->password = $newPassword;

        $user->save();

        return true;
    }

    /**
     * delete user account
     */
    public function deleteAccount(Request $request, User $user, string $password): bool
    {
        if (!Hash::check($password, $user->password)) {
            return false;
        }

        Auth::guard('web')->logout();

        $request->session()->invalidate();
        
        $request->session()->regenerateToken();

        $user->delete();

        return true;
    }
}
