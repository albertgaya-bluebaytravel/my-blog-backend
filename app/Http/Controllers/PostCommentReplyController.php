<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use App\Services\PostCommentReplyService;
use App\Http\Requests\Posts\PostCommentReplyStoreRequest;

class PostCommentReplyController extends Controller
{
    protected PostCommentReplyService $postCommentReplyService;

    public function __construct(PostCommentReplyService $postCommentReplyService)
    {
        $this->postCommentReplyService = $postCommentReplyService;
    }

    /**
     * Store post comment reply
     * 
     * @param PostCommentReplyStoreRequest $request
     * @param Post $post
     * @param Comment $comment
     * @return JsonResponse
     */
    public function store(PostCommentReplyStoreRequest $request, Post $post, Comment $comment): JsonResponse
    {
        $comment = $this->postCommentReplyService->store($request->validated(), $post, $comment, Auth::user());

        return Response::jsonSuccess(['comment' => $comment]);
    }
}
