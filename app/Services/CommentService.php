<?php

namespace App\Services;

use App\Models\Comment;

class CommentService
{
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
}
