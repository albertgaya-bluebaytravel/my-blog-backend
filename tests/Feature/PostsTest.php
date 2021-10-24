<?php

namespace Tests\Feature;

use App\Enums\DirectoryEnum;
use App\Enums\DiskEnum;
use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

/** @group PostsTest */
class PostsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Pre filled data
     * 
     * @param array $override
     * @return array
     */
    private function data(array $override = []): array
    {
        return $override + [
            'title' => $this->faker->title,
            'body' => $this->faker->paragraph,
            'image' => UploadedFile::fake()->image('post.jpg')
        ];
    }

    /** @test */
    public function get_posts(): void
    {
        $user = User::factory()->create();
        $posts = Post::factory()->count(10)->create(['user_id' => $user]);

        $response = $this->getJson($this->uri('/posts'))
            ->assertOk();

        $data = $this->assertSuccessJsonResponse($response)['data'];

        $this->assertArrayHasKey('posts', $data);

        $dataPosts = $data['posts'];
        $this->assertSameSize($posts, $dataPosts);

        $dataPost = current($dataPosts);
        $post = $posts->last();
        $this->assertEquals($post->id, $dataPost['id']);
        $this->assertEquals($post->imageFullUrl, $dataPost['image_full_url']);

        $this->assertArrayHasKey('user', $dataPost);
        $dataUser = $dataPost['user'];
        $this->assertEquals($user->id, $dataUser['id']);
    }

    /** @test */
    public function get_single_post(): void
    {
        $post = Post::factory()->create();

        $response = $this->getJson($this->uri("/posts/{$post->id}"))
            ->assertOk();

        $data = $this->assertSuccessJsonResponse($response)['data'];

        $this->assertArrayHasKey('post', $data);

        $dataPost = $data['post'];
        $this->assertEquals($post->id, $dataPost['id']);
        $this->assertArrayHasKey('user', $dataPost);
    }

    /** @test */
    public function post_posts_non_authorized_user(): void
    {
        $this->postJson($this->uri('/posts'))
            ->assertUnauthorized();
    }

    /** @test */
    public function post_posts_required_parameters(): void
    {
        $this->createSigninUser();
        $response = $this->postJson($this->uri('/posts'))
            ->assertUnprocessable();

        $errors = $this->assertErrorJsonResponse($response)['errors'];
        $this->assertCount(2, $errors);
        $this->assertArrayHasKey('title', $errors);
        $this->assertArrayHasKey('body', $errors);
    }

    /** @test */
    public function post_posts(): void
    {
        Storage::fake(DiskEnum::PUBLIC);

        $user = $this->createSigninUser();
        $param = $this->data();
        $response = $this->postJson($this->uri('/posts'), $param)
            ->assertOk();

        $data = $this->assertSuccessJsonResponse($response)['data'];

        $this->assertArrayHasKey('post', $data);
        $dataPost = $data['post'];

        $this->assertEquals($param['title'], $dataPost['title']);
        $this->assertEquals($param['body'], $dataPost['body']);
        $this->assertEquals(DirectoryEnum::POSTS . '/' . $param['image']->hashName(), $dataPost['image_url']);

        $post = Post::find($dataPost['id']);
        $this->assertTrue($post->user->is($user));
        $this->assertCount(1, $user->posts);

        Storage::disk(DiskEnum::PUBLIC)->assertExists(DirectoryEnum::POSTS . '/' . $param['image']->hashName());
    }

    /** @test */
    public function post_posts_image_as_null(): void
    {
        $this->createSigninUser();
        $response = $this->postJson($this->uri('/posts'), ['image' => null] + $this->data())
            ->assertOk();

        $this->assertSuccessJsonResponse($response);
    }

    /** @test */
    public function patch_single_post_non_signin_user(): void
    {
        $post = Post::factory()->create();

        $response = $this->patchJson($this->uri("/posts/{$post->id}"))
            ->assertForbidden();

        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function patch_single_post_non_authorized_user(): void
    {
        $this->createSigninUser();
        $post = Post::factory()->create();

        $response = $this->patchJson($this->uri("/posts/{$post->id}"))
            ->assertForbidden();

        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function patch_single_post(): void
    {
        Storage::fake('public');

        $user = $this->createSigninUser();
        $post = Post::factory()->create(['user_id' => $user]);

        $param = [
            'title' => $this->faker->title,
            'body' => $this->faker->paragraph,
            'image' => UploadedFile::fake()->image('post_updated.jpg')
        ];

        $response = $this->patchJson($this->uri("/posts/{$post->id}"), $param)
            ->assertOk();

        $data = $this->assertSuccessJsonResponse($response)['data'];

        $this->assertArrayHasKey('post', $data);
        $dataPost = $data['post'];
        $post->refresh();
        $this->assertEquals($post->id, $dataPost['id']);
        $this->assertEquals($post->title, $param['title']);
        $this->assertEquals($post->body, $param['body']);
        $this->assertEquals(DirectoryEnum::POSTS . '/' . $param['image']->hashName(), $dataPost['image_url']);

        Storage::disk(DiskEnum::PUBLIC)->assertExists(DirectoryEnum::POSTS . '/' . $param['image']->hashName());
    }

    /** @test */
    public function patch_signel_post_image_as_null(): void
    {
        $user = $this->createSigninUser();
        $post = Post::factory()->create(['user_id' => $user]);

        $param = [
            'title' => $this->faker->title,
            'body' => $this->faker->paragraph,
            'image' => null
        ];

        $response = $this->patchJson($this->uri("/posts/{$post->id}"), $param)
            ->assertOk();

        $dataPost = $this->assertSuccessJsonResponse($response)['data']['post'];
        $this->assertNotNull($dataPost['image_url']);
    }

    /** @test */
    public function delete_single_post_non_signin_user(): void
    {
        $post = Post::factory()->create();

        $response = $this->deleteJson($this->uri("/posts/{$post->id}"))
            ->assertForbidden();

        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function delete_single_post_non_authorized_user(): void
    {
        $this->createSigninUser();
        $post = Post::factory()->create();

        $response = $this->deleteJson($this->uri("/posts/{$post->id}"))
            ->assertForbidden();

        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function delete_single_post(): void
    {
        $user = $this->createSigninUser();
        $post = Post::factory()->create(['user_id' => $user]);
        $comment = Comment::factory()->create(['user_id' => $user, 'post_id' => $post]);

        $response = $this->deleteJson($this->uri("/posts/{$post->id}"))
            ->assertOk();

        $this->assertSuccessJsonResponse($response);
        $this->assertNull($post->fresh());
        $this->assertNull($comment->fresh());
    }
}
