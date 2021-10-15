<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;

class PostService
{
    public function store(array $data, User $user): Post
    {
        $post = new Post($data);
        $post->user_id = $user->id;
        $post->save();

        return $post;
    }
}
