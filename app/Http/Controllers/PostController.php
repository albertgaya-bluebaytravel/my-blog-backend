<?php

namespace App\Http\Controllers;

use App\Http\Requests\Posts\PostStoreRequest;
use App\Models\Post;
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

    /**
     * Get list of Post
     * 
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $query = Post::query()
            ->with('user')
            ->orderBy('id', 'desc');

        $posts = $this->postService->all($query);

        return Response::jsonSuccess(['posts' => $posts]);
    }

    /**
     * Get single Post
     * 
     * @param Post $post
     * @return JsonResponse
     */
    public function show(Post $post): JsonResponse
    {
        return Response::jsonSuccess(['post' => $post]);
    }

    /**
     * Create new post
     * 
     * @param PostStoreRequest $request
     * @return JsonResponse
     */
    public function store(PostStoreRequest $request): JsonResponse
    {
        $post = $this->postService->store($request->validated(), Auth::user());

        return Response::jsonSuccess(['post' => $post]);
    }
}
