<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Services\PostService;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Posts\PostStoreRequest;
use App\Http\Requests\Posts\PostUpdateRequest;
use App\Http\Requests\Posts\PostDestroyRequest;

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
        return Response::jsonSuccess(['post' => $post->load('user')]);
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

    /**
     * Update post
     * 
     * @param PostUpdateRequest $request
     * @param Post $post
     * @return JsonResponse
     */
    public function update(PostUpdateRequest $request, Post $post): JsonResponse
    {
        $this->postService->update($post, $request->validated());

        return Response::jsonSuccess(['post' => $post]);
    }

    /**
     * Delete post
     * 
     * @param PostDestroyRequest $request
     * @param Post $post
     * @return JsonResponse
     */
    public function destroy(PostDestroyRequest $request, Post $post): JsonResponse
    {
        $this->postService->delete($post);

        return Response::jsonSuccess([], 'Successfully deleted Post.');
    }
}
