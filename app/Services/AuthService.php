<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use App\Exceptions\AuthException;
use App\Models\User;

class AuthService
{
    /**
     * login user
     */
    public function login(array $formData): ?User
    {
        if (!Auth::attempt($formData)) {
            throw AuthException::invalidCredentials($formData['email']);
        }

        return Auth::user();
    }
}
