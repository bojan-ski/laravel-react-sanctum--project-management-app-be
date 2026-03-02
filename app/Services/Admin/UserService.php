<?php

namespace App\Services\Admin;

use Illuminate\Pagination\LengthAwarePaginator;
use App\Exceptions\UserException;
use App\Services\Mail\MailService;
use App\Enums\UserRole;
use App\Models\User;

class UserService
{
    public function __construct(protected readonly MailService $mailService) {}

    /**
     * Get users - excluding admin
     */
    public function getAllUsers(
        ?string $search,
        int $perPage = 12
    ): LengthAwarePaginator {
        return User::query()
            ->where('role', UserRole::USER)
            ->when(
                $search,
                fn($query, $search) => $query->where(
                    fn($q) => $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                )
            )
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a new user and send credentials email
     */
    public function createUser(array $formData): User
    {
        $name = $formData['name'];
        $email = $formData['email'];
        $password = $formData['password'];

        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ]);
        } catch (\Throwable $e) {
            throw UserException::addNewUserFailed($email, $e);
        }

        $this->mailService->sendCredentialsEmail($user, $password);

        return $user;
    }

    /**
     * Get single user details.
     */
    public function getUserDetails(User $user): User
    {
        return $user->loadCount(['ownedProjects', 'memberProjects'])
            ->load(['projects']);
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user): void
    {
        try {
            $user->delete();
        } catch (\Throwable $e) {
            throw UserException::deleteUserFailed($user->id, $e);
        }
    }
}
