<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;
use App\Mail\UserCredentialsMail;
use App\Models\User;

class UserService
{
    /**
     * Get paginated list of users - excluding admin
     */
    public function getAllUsers(string | null $search): LengthAwarePaginator
    {
        return User::query()
            ->where('role', 'user')
            ->when(
                $search,
                fn($query, $search) =>
                $query->where(
                    fn($q) =>
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                )
            )
            ->latest()
            ->paginate(2);
    }

    /**
     * Create a new user and send credentials email
     */
    public function createUser(array $data): ?User
    {
        $password = $data['password'];

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $password,
            // 'password' => Hash::make($data['password']),
        ]);

        return $this->sendCredentialsEmail($user, $password);
    }

    /**
     * Send credentials to newly created user
     */
    private function sendCredentialsEmail(User $user, string $password)
    {
        try {
            Mail::to($user->email)->send(new UserCredentialsMail($user, $password));
            // Mail::to($user->email)->queue(new UserCredentialsMail($user, $password));

            return $user;
        } catch (\Throwable $th) {
            Log::error('Project creation failed', ['error' => $th->getMessage()]);

            $user->delete();

            return null;
        }
    }

    /**
     * Delete user
     */
    public function deleteUser(User $user): void
    {
        $user->delete();
    }
}
