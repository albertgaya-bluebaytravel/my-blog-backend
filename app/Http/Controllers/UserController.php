<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\UserLoginRequest;
use App\Http\Requests\Users\UserRegisterRequest;
use App\Services\UserService;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Register a user
     * 
     * @param UserRegisterRequest $request
     * @return JsonResponse
     */
    public function register(UserRegisterRequest $request): JsonResponse
    {
        $user = $this->userService->store($request->validated());

        return Response::jsonSuccess(['user' => $user]);
    }

    /**
     * Verify user account
     * 
     * @param User $user
     * @return JsonResponse
     */
    public function verify(User $user): JsonResponse
    {
        $this->userService->verify($user);

        return Response::jsonSuccess([], 'Email address successfully verified!');
    }

    /**
     * Login user
     * 
     * @param UserLoginRequest $request
     * @return JsonResponse
     */
    public function login(UserLoginRequest $request): JsonResponse
    {
        $token = $this->userService->login($request->email, $request->password);

        return Response::jsonSuccess(['token' => $token]);
    }

    /**
     * Get Authenticated User
     * 
     * @return JsonResponse
     */
    public function auth(): JsonResponse
    {
        return Response::jsonSuccess(['user' => Auth::user()]);
    }
}
