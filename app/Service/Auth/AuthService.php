<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;

class AuthService
{
    /**
     * login user
     */
    public function login(array $data): ?User
    {
        if (!Auth::attempt($data)) {
            return null;
        }

        return Auth::user();
    }

    /**
     * logout user
     */
    public function logout(Request $request): void
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();
    }
}
