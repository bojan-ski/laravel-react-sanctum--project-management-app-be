<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use App\Mail\UserCredentialsMail;
use App\Models\User;

class UserService
{
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
        } catch (\Exception $e) {
            $user->delete();

            return null;
        }
    }
}
