<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Exceptions\AuthException;
use App\Services\AuthService;
use App\Traits\ApiResponse;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(private AuthService $authService) {}

    /**
     * Store a newly created resource in storage - login feature.
     */
    public function login(LoginRequest $request): JsonResponse
    {

        try {
            $user = $this->authService->login($request->validated());

            return $this->success(
                message: 'Login successful',
                data: new UserResource($user),
            );
        } catch (AuthException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: 401
            );
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function logout(): JsonResponse
    {
        Auth::guard('web')->logout();

        session()->invalidate();
        session()->regenerateToken();

        return $this->success(
            message: 'Logged out successfully'
        );
    }
}
