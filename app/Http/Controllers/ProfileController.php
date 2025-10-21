<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Profile\ChangePasswordRequest;
use App\Http\Requests\Profile\DeleteAccountRequest;
use App\Services\ProfileService;
use App\Traits\ApiResponse;

class ProfileController extends Controller
{
    use ApiResponse;

    public function __construct(private ProfileService $profileService) {}

    /**
     * Change user password
     */
    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $response = $this->profileService->changePassword(
            $request->user(),
            $request->old_password,
            $request->new_password
        );

        if (!$response) {
            return $this->error('Wrong password', 400);
        }

        return $this->success(null, 'Password changed successfully');
    }

    /**
     * Delete user account
     */
    public function deleteAccount(DeleteAccountRequest $request): JsonResponse
    {
        $response = $this->profileService->deleteAccount(
            $request,
            $request->user(),
            $request->password
        );

        if (!$response) {
            return $this->error('Password incorrect!', 400);
        }

        return $this->success(null, 'Your account has been deleted');
    }
}
