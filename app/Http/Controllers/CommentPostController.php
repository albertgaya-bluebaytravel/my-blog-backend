<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;
use App\Services\CommentPostService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Comments\CommentStoreRequest;
use App\Models\Comment;

class CommentPostController extends Controller
{
    protected CommentPostService $commentPostService;

    public function __construct(CommentPostService $commentPostService)
    {
        $this->commentPostService = $commentPostService;
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
            ->orderByDesc('id');

        $comments = $this->commentPostService->show($query, $post);

        return Response::jsonSuccess(['comments' => $comments]);
    }

    /**
     * Store Post Comment
     * 
     * @param CommentStoreRequest $request
     * @param Post $post
     * @return JsonResponse
     */
    public function store(CommentStoreRequest $request, Post $post): JsonResponse
    {
        $comment = $this->commentPostService->store($request->validated(), $post);

        return Response::jsonSuccess(['comment' => $comment]);
    }
}
