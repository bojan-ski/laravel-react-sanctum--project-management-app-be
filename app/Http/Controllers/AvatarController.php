<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use App\Http\Requests\Profile\AvatarRequest;
use App\Http\Resources\UserResource;
use App\Exceptions\AvatarException;
use App\Services\AvatarService;
use App\Traits\ApiResponse;

class AvatarController extends Controller
{
    use ApiResponse;

    public function __construct(protected readonly AvatarService $avatarService) {}

    /**
     * Upload user avatar
     */
    public function updateUserAvatar(AvatarRequest $request): JsonResponse
    {
        $user = $request->user();
        $avatar = $request->validated('avatar');

        try {
            $this->avatarService->processUserAvatar(
                $user,
                $avatar
            );

            return $this->success(
                message: 'Avatar updated',
                data: new UserResource($user)
            );
        } catch (AvatarException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: $e->getStatusCode()
            );
        }
    }
}
