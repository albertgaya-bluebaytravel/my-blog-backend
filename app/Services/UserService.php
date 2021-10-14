<?php

namespace App\Services;

use App\Mail\NewUserEmailVerification;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

class UserService
{
    public function store(array $data): User
    {
        $user = User::create($data);

        Mail::to($user)->send(new NewUserEmailVerification());

        return $user;
    }
}
