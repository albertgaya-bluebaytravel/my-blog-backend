<?php

namespace App\Services;

use App\Models\Post;
use App\Models\User;
use App\Enums\DiskEnum;
use App\Enums\DirectoryEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class PostService
{
    /**
     * Upload Post image
     * 
     * @param array &$data
     * @return void
     */
    protected function uploadImage(array &$data): void
    {
        if (isset($data['image'])) {
            $data['image_url'] = $data['image']->store(DirectoryEnum::POSTS, DiskEnum::PUBLIC);
        }

        unset($data['image']);
    }

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
        $this->uploadImage($data);

        $post = new Post($data);
        $post->user_id = $user->id;
        $post->save();

        return $post;
    }

    /**
     * Update Post data
     * 
     * @param Post $post
     * @param array $data
     * @return bool
     */
    public function update(Post $post, array $data): bool
    {
        if (isset($data['image_remove']) && $data['image_remove']) {
            unset($data['image'], $data['image_remove']);
            $data['image_url'] = null;
        } else {
            $this->uploadImage($data);
        }

        $this->uploadImage($data);

        return $post->update($data);
    }

    /**
     * Delete Post
     * 
     * @param Post $post
     * @return bool
     */
    public function delete(Post $post): bool
    {
        return $post->delete();
    }
}
