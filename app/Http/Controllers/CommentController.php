<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Response;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Comments\CommentStoreRequest;

class CommentController extends Controller
{
    protected CommentService $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
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
        $comment = $this->commentService->store($request->validated(), $post);

        return Response::jsonSuccess(['comment' => $comment]);
    }
}
