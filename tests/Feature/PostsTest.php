<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Post;
use App\Models\Comment;
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
    public function post_posts_comments_required_parameters(): void
    {
        $this->createSigninUser();
        $post = Post::factory()->create();
        $response = $this->postJson($this->uri("/posts/{$post->id}/comments"))
            ->assertUnprocessable();

        $errors = $this->assertErrorJsonResponse($response)['errors'];
        $this->assertCount(1, $errors);
        $this->assertArrayHasKey('body', $errors);
    }

    /** @test */
    public function post_posts_comments_invalid_post(): void
    {
        $this->createSigninUser();
        $this->postJson($this->uri('/posts/123/comments'))
            ->assertNotFound();
    }

    /** @test */
    public function post_posts_comments(): void
    {
        $this->createSigninUser();
        $post = Post::factory()->create();
        $param = ['body' => $this->faker->paragraph];
        $response = $this->postJson($this->uri("/posts/{$post->id}/comments"), $param)
            ->assertOk();

        $data = $this->assertSuccessJsonResponse($response)['data'];

        $this->assertArrayHasKey('comment', $data);
        $dataComment = $data['comment'];

        $comment = Comment::find($dataComment['id']);
        $this->assertNotNull($comment);

        $this->assertEquals($param['body'], $dataComment['body']);

        $this->assertTrue($comment->post->is($post));
        $this->assertCount(1, $post->comments);
    }
}
