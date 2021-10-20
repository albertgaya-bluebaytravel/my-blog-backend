<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class PostService
{
    /**
     * List of Post
     * 
     * @return Collection
     */
    public function all(): Collection
    {
        return Post::all();
    }

    /**
     * Create Post data
     * 
     * @param array $data
     * @param User $user
     * @return Post
     */
    public function store(array $data, User $user): Post
    {
        $post = new Post($data);
        $post->user_id = $user->id;
        $post->save();

        return $post;
    }
}
