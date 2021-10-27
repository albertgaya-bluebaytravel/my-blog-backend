<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Response;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\Comments\CommentUpdateRequest;
use App\Http\Requests\Comments\CommentDestroyRequest;

class CommentController extends Controller
{
    protected CommentService $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    /**
     * Update comment
     * 
     * @param CommentUpdateRequest $request
     * @param Comment $coment
     * @return JsonResponse
     */
    public function update(CommentUpdateRequest $request, Comment $comment): JsonResponse
    {
        $this->commentService->update($comment, $request->validated());

        return Response::jsonSuccess(['comment' => $comment]);
    }

    /**
     * Delete comment
     * 
     * @param CommentDestroyRequest $request
     * @param Comment $comment
     * @return JsonResponse
     */
    public function destroy(CommentDestroyRequest $request, Comment $comment): JsonResponse
    {
        $this->commentService->delete($comment);

        return Response::jsonSuccess([], 'Successfully deleted Post.');
    }
}
