<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Profile\AvatarRequest;
use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\DeleteAccountRequest;
use App\Http\Resources\UserResource;
use App\Exceptions\ProfileException;
use App\Services\ProfileService;
use App\Traits\ApiResponse;

class ProfileController extends Controller
{
    use ApiResponse;

    public function __construct(protected readonly ProfileService $profileService) {}

    /**
     * Get current user profile
     */
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->success(
            message: 'Profile retrieved successfully',
            data: new UserResource($user)
        );
    }

    /**
     * Upload user avatar
     */
    public function uploadAvatar(AvatarRequest $request): JsonResponse
    {
        try {
            $updatedUser = $this->profileService->updateProfile(
                user: $request->user(),
                avatar: $request->file('avatar')
            );

            return $this->success(
                message: 'Avatar updated',
                data: new UserResource($updatedUser)
            );
        } catch (ProfileException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: $e->getStatusCode()
            );
        }
    }

    /**
     * Change user password
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        try {
            $this->profileService->validateUserPassword(
                user: $request->user(),
                oldPassword: $request->validated('old_password')
            );

            $this->profileService->changePassword(
                user: $request->user(),
                newPassword: $request->validated('new_password')
            );

            return $this->success(message: 'Password changed successfully');
        } catch (ProfileException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: $e->getStatusCode()
            );
        }
    }

    /**
     * Delete user account
     */
    public function destroy(DeleteAccountRequest $request): JsonResponse
    {
        try {
            $this->profileService->validateUserPassword(
                user: $request->user(),
                oldPassword: $request->validated('password')
            );

            Auth::guard('web')->logout();

            session()->invalidate();
            session()->regenerateToken();

            $this->profileService->deleteAccount($request->user());

            return $this->success(message: 'Your account has been deleted');
        } catch (ProfileException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: $e->getStatusCode()
            );
        }
    }
}
