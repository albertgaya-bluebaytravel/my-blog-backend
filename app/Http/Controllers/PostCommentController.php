<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Response;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use App\Services\PostCommentService;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Posts\PostCommentStoreRequest;
use App\Http\Requests\Posts\PostCommentUpdateRequest;
use App\Http\Requests\Posts\PostCommentDestroyRequest;

class PostCommentController extends Controller
{
    protected PostCommentService $postCommentService;
    protected CommentService $commentService;

    public function __construct(PostCommentService $postCommentService, CommentService $commentService)
    {
        $this->postCommentService = $postCommentService;
        $this->commentService = $commentService;
    }

    /**
     * Get list of post comments
     * 
     * @param Post $post
     * @return JsonResponse
     */
    public function index(Post $post): JsonResponse
    {
        $query = Comment::query()
            ->with('user')
            ->with('replies')
            ->doesntHave('parent')
            ->orderByDesc('id');

        $comments = $this->postCommentService->all($query, $post);

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

    /**
     * Update Post Comment
     * 
     * @param PostCommentUpdateRequest $request
     * @param Post $post
     * @param Comment $comment
     * @return JsonResponse
     */
    public function update(PostCommentUpdateRequest $request, Post $post, Comment $comment): JsonResponse
    {
        $this->commentService->update($comment, $request->validated());

        return Response::jsonSuccess(['comment' => $comment]);
    }

    /**
     * Delete Post Comment
     * 
     * @param PostCommentDestroyRequest $request
     * @param Post $post
     * @param Comment $comment
     * @return JsonResponse
     */
    public function destroy(PostCommentDestroyRequest $request, Post $post, Comment $comment): JsonResponse
    {
        $this->commentService->delete($comment);

        return Response::jsonSuccess([], 'Successfully deleted Post.');
    }
}
