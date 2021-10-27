<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Models\Comment;

class PostCommentReplyService
{
    /**
     * Store post comment reply
     * 
     * @param array $data
     * @param Post $post
     * @param Comment $parentComment
     * @param User $user
     * @return Comment
     */
    public function store(array $data, Post $post, Comment $parentComment, User $user): Comment
    {
        $comment = new Comment($data);
        $comment->post()->associate($post);
        $comment->parent()->associate($parentComment);
        $comment->user()->associate($user);
        $comment->save();

        return $comment;
    }
}
