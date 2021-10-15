<?php

namespace App\Services;

use App\Models\Post;
use App\Models\Comment;

class CommentService
{
    /**
     * Create a post comment
     * 
     * @param array $data
     * @param Post $post
     * @return Comment
     */
    public function store(array $data, Post $post): Comment
    {
        $comment = new Comment($data);
        $comment->post_id = $post->id;
        $comment->save();

        return $comment;
    }
}
