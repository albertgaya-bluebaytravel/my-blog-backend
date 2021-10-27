<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Services\PostCommentService;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Posts\PostCommentStoreRequest;

class PostCommentController extends Controller
{
    protected PostCommentService $postCommentService;

    public function __construct(PostCommentService $postCommentService)
    {
        $this->postCommentService = $postCommentService;
    }

    /**
     * Show single post comments
     * 
     * @param Post $post
     * @return JsonResponse
     */
    public function show(Post $post): JsonResponse
    {
        $query = Comment::query()
            ->with('user')
            ->orderByDesc('id');

        $comments = $this->postCommentService->show($query, $post);

        return Response::jsonSuccess(['comments' => $comments]);
    }

    /**
     * Store Post Comment
     * 
     * @param PostCommentStoreRequest $request
     * @param Post $post
     * @return JsonResponse
     */
    public function store(PostCommentStoreRequest $request, Post $post): JsonResponse
    {
        $comment = $this->postCommentService->store($request->validated(), $post, Auth::user());

        return Response::jsonSuccess(['comment' => $comment]);
    }
}
