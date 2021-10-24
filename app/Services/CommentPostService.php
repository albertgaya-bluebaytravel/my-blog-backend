<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class CommentPostService
{
    /**
     * Show a post comments
     * 
     * @param Builder $query
     * @param Post $post
     * @return Collection
     */
    public function show(Builder $query = null, Post $post): Collection
    {
        if (!$query) {
            $query = Comment::query();
        }

        $query->whereBelongsTo($post);

        return $query->get();
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
        $comment = new Comment($data);
        $comment->post_id = $post->id;
        $comment->user_id = $user->id;
        // $comment->post()->associate($post);
        // $comment->user()->associate($user);
        $comment->save();

        return $comment;
    }
}
