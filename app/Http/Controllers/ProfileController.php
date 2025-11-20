<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Profile\AvatarRequest;
use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\DeleteAccountRequest;
use App\Services\ProfileService;
use App\Traits\ApiResponse;

class ProfileController extends Controller
{
    use ApiResponse;

    public function __construct(private ProfileService $profileService) {}

    /**
     * Upload user avatar
     */
    public function uploadAvatar(AvatarRequest $request): JsonResponse
    {
        $user = $request->user();
        $avatar = $request->file('avatar');

        // call profile service
        $updatedUser = $this->profileService->uploadAvatar(
            $user,
            $avatar
        );

        // return json
        if (!$updatedUser) {
            return $this->error('Failed to upload avatar!', 500);
        }

        return $this->success($updatedUser, 'Avatar updated');
    }

    /**
     * Change user password
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();
        $formData = $request->validated();

        // check password
        if (!Hash::check($formData['old_password'], $user->password)) {
            return $this->error('Wrong password', 400);
        };

        // call profile service
        $response = $this->profileService->changePassword(
            $request->user(),
            $formData['new_password']
        );

        // return json
        if (!$response) {
            return $this->error('Failed to change password!', 500);
        }

        return $this->success(null, 'Password changed successfully');
    }

    /**
     * Delete user account
     */
    public function deleteAccount(DeleteAccountRequest $request): JsonResponse
    {
        $user = $request->user();

        // check password
        if (!Hash::check($request->password, $user->password)) {
            return $this->error('Password incorrect!', 400);
        }

        // call profile service
        $response = $this->profileService->deleteAccount(
            $user
        );

        // return json
        if (!$response) {
            return $this->error('Failed to delete account!', 500);
        }

        return $this->success(null, 'Your account has been deleted');
    }
}
