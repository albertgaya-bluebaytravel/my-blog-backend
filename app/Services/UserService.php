<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Crypt;

class UserService
{
    public function store(array $data): User
    {
        $user = User::create($data);
        $user->sendEmailVerificationNotification();
        return $user;
    }

    public function verify(string $encryptedUserId): void
    {
        $id = Crypt::decrypt($encryptedUserId);

        $user = User::findOrFail($id);
        $user->markEmailAsVerified();
    }
}
