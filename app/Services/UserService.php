<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class UserService
{
    /**
     * Store user
     * 
     * @param array $data
     * @param User
     */
    public function store(array $data): User
    {
        $user = User::create($data);
        $user->sendEmailVerificationNotification();
        return $user;
    }

    /**
     *  Verify user email
     * 
     * @param User $user
     * @return void
     */
    public function verify(User $user): void
    {
        if ($user->hasVerifiedEmail()) {
            throw new UnprocessableEntityHttpException('User email already been verified!');
        }

        $user->markEmailAsVerified();
    }

    /**
     * Generate login token
     * 
     * @param string $email
     * @param string $password
     * @return string
     */
    public function login(string $email, string $password): string
    {
        $user = User::where('email', $email)->first();

        if (!$user || !$user->is_active || !Hash::check($password, $user->password)) {
            throw new UnprocessableEntityHttpException('The provided credentials are incorrect!');
        }

        return $user->generateToken();
    }
}
