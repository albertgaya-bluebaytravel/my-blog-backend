<?php

namespace App\Services;

use App\Models\User;
use App\Models\Comment;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;

class CommentService
{
    /**
     * All comments
     * 
     * @param Builder $query
     * @return Collection
     */
    public function all(Builder $query = null): Collection
    {
        if (!$query) {
            $query = Comment::query();
        }

        return $query->get();
    }

    /**
     * Create a comment
     * 
     * @param array $data
     * @param User $user
     * @return Comment
     */
    public function store(array $data, User $user): Comment
    {
        $comment = new Comment($data);
        $comment->user()->associate($user);
        $comment->save();

        return $comment;
    }

    /**
     * Update comment
     * 
     * @param Comment $comment
     * @param Array $data
     * @return bool
     */
    public function update(Comment $comment, array $data): bool
    {
        return $comment->update($data);
    }

    /**
     * Delete comment
     * 
     * @param Comment $coment
     * @return bool
     */
    public function delete(Comment $comment): bool
    {
        return $comment->delete();
    }
}
