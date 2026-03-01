<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Admin\SearchRequest;
use App\Http\Requests\Admin\CreateUserRequest;
use App\Exceptions\UserException;
use App\Http\Resources\UserResource;
use App\Http\Resources\Admin\UserDetailResource;
use App\Services\Admin\UserService;
use App\Traits\ApiResponse;
use App\Models\User;

class UserController extends Controller
{
    use ApiResponse;

    public function __construct(protected readonly UserService $userService) {}

    /**
     * Display a listing of the resource.
     */
    public function index(SearchRequest $request): JsonResponse
    {
        $users = $this->userService->getAllUsers(
            search: $request->validated('search') ?? null,
            perPage: 12
        );

        $users->setCollection(
            UserResource::collection($users)->collection
        );

        return $this->success(
            message: 'All user retrieved',
            data: $users
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(CreateUserRequest $request): JsonResponse
    {
        try {
            $user = $this->userService->createUser($request->validated());

            return $this->success(
                message: 'New user created',
                data: $user,
                statusCode: 201
            );
        } catch (UserException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: $e->getStatusCode()
            );
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): JsonResponse
    {
        $user = $this->userService->getUserDetails($user);

        return $this->success(
            message: 'User details retrieved',
            data: new UserDetailResource($user)
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        try {
            $this->userService->deleteUser($user);

            return $this->success(
                message: 'User deleted',
                data: [
                    'id' => $user->id
                ]
            );
        } catch (UserException $e) {
            $e->report();
            return $this->error(
                message: $e->getMessage(),
                statusCode: $e->getStatusCode()
            );
        }
    }
}
