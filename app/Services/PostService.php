<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PostService
{
    /**
     * List of Post
     * 
     * @param Builder $query
     * @return Collection
     */
    public function all(Builder $query = null): Collection
    {
        if (!$query) {
            $query = Post::query();
        }

        return $query->get();
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
