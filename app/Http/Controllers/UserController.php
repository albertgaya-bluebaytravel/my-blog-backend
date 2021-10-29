<?php

namespace App\Http\Controllers;

use App\Http\Requests\Users\UserLoginRequest;
use App\Http\Requests\Users\UserRegisterRequest;
use App\Http\Requests\Users\UserUniqueProfileRequest;
use App\Http\Requests\Users\UserUpdateProfileRequest;
use App\Services\UserService;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Models\User;
use Illuminate\Http\Request;

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
     * @param string $token
     * @return JsonResponse
     */
    public function verify(User $user, string $token): JsonResponse
    {
        $this->userService->verify($user, $token);

        return Response::jsonSuccess([], 'Email address successfully verified!');
    }

    /**
     * Login user
     * 
     * @param UserLoginRequest $request
     * @return void
     */
    public function login(UserLoginRequest $request): void
    {
        $this->userService->login($request->email, $request->password);
    }

    /**
     * Get Authenticated User
     * 
     * @return JsonResponse
     */
    public function auth(Request $request): JsonResponse
    {
        return Response::jsonSuccess(['user' => $request->user()]);
    }

    /**
     * Update user profile
     * 
     * @param UserUpdateProfileRequest $request
     * @param User $user
     * @return JsonResponse
     */
    public function updateProfile(UserUpdateProfileRequest $request, User $user): JsonResponse
    {
        $this->userService->updateProfile($user, $request->validated());

        return Response::jsonSuccess(['user' => $user]);
    }

    /**
     * Check if user emaii is unique
     * 
     * @param UserUniqueProfileRequest $request
     * @return JsonResponse
     */
    public function isUniqueEmail(UserUniqueProfileRequest $request): JsonResponse
    {
        $isUnique = $this->userService->isUniqueEmail($request->get('email'));

        return Response::jsonSuccess($isUnique);
    }
}
