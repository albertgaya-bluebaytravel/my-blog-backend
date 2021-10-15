<?php

namespace App\Http\Controllers;

use App\Http\Requests\PostStoreRequest;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    protected PostService $postService;

    public function __construct(PostService $postService)
    {
        $this->postService = $postService;
    }

    public function store(PostStoreRequest $request): JsonResponse
    {
        $post = $this->postService->store($request->validated(), Auth::user());

        return Response::jsonSuccess(['post' => $post]);
    }
}
