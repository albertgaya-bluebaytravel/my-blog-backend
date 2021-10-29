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
     * @return User
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
     * Update user
     * 
     * @param User $user
     * @param array $data
     * @return bool
     */
    public function update(User $user, array $data): bool
    {
        return $user->update($data);
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
        if (!Auth::attempt(['email' => $email, 'password' => $password, 'is_active' => 1])) {
            throw new UnprocessableEntityHttpException('The provided credentials are incorrect!');
        }
    }

    /**
     * Update user profile
     * 
     * @param User $user
     * @param array $data
     * @return bool
     */
    public function updateProfile(User $user, array $data): bool
    {
        if (!$data['password_change']) {
            unset($data['password']);
        }

        unset($data['password_change']);

        return $this->update($user, $data);
    }

    /**
     * Check if email is unique
     * 
     * @param string $email
     * @param bool
     */
    public function isUniqueEmail(string $email): bool
    {
        return !User::where('email', $email)->exists();
    }
}
