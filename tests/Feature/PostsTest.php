<?php

namespace Tests\Feature;

use App\Models\Comment;
use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
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
        $this->assertEquals($posts->last()->id, $dataPost['id']);

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
        $user = $this->createSigninUser();
        $param = $this->data();
        $response = $this->postJson($this->uri('/posts'), $param)
            ->assertOk();

        $data = $this->assertSuccessJsonResponse($response)['data'];

        $this->assertArrayHasKey('post', $data);
        $dataPost = $data['post'];

        $post = Post::find($dataPost['id']);
        $this->assertNotNull($user);

        $this->assertEquals($param['title'], $dataPost['title']);
        $this->assertEquals($param['body'], $dataPost['body']);

        $this->assertTrue($post->user->is($user));
        $this->assertCount(1, $user->posts);
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
        $user = $this->createSigninUser();
        $post = Post::factory()->create(['user_id' => $user]);

        $param = [
            'title' => $this->faker->title,
            'body' => $this->faker->paragraph,
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
