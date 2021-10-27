<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use App\Services\CommentService;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class PostCommentService
{
    protected CommentService $commentService;

    public function __construct(CommentService $commentService)
    {
        $this->commentService = $commentService;
    }

    /**
     * All post comments
     * 
     * @param Builder $query
     * @param Post $post
     * @return Collection
     */
    public function all(Builder $query = null, Post $post): Collection
    {
        if (!$query) {
            $query = Comment::query();
        }

        $query->whereBelongsTo($post);

        return $this->commentService->all($query);
    }

    /**
     * Create a post comment
     * 
     * @param array $data
     * @param Post $post
     * @param User $user
     * @return Comment
     */
    public function store(array $data, Post $post, User $user): Comment
    {
        $data['post_id'] = $post->id;

        return $this->commentService->store($data, $user);
    }
}
