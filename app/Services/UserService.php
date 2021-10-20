<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
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
        $user = new User($data);
        $user->email_verification_token = Str::random();
        $user->save();
        $user->sendEmailVerificationNotification();
        return $user;
    }

    /**
     *  Verify user email
     * 
     * @param User $user
     * @return void
     */
    public function verify(User $user, string $token): void
    {
        if ($user->hasVerifiedEmail()) {
            throw new UnprocessableEntityHttpException('User email already been verified!');
        }

        if ($user->email_verification_token !== $token) {
            throw new UnprocessableEntityHttpException('Invalid email verification token!');
        }

        $user->markEmailAsVerified();
    }

    /**
     * Generate login token
     * 
     * @param string $email
     * @param string $password
     * @return void
     */
    public function login(string $email, string $password): void
    {
        if (!Auth::attempt(['email' => $email, 'password' => $password])) {
            throw new UnprocessableEntityHttpException('The provided credentials are incorrect!');
        }
    }
}
