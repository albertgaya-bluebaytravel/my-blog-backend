<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

/** @group CommentTest */
class CommentTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function get_single_post_comments(): void
    {
        $post = Post::factory()->create();
        $comments = Comment::factory()->count(10)->create(['post_id' => $post]);

        $response = $this->getJson($this->uri("/comments/posts/{$post->id}"))
            ->assertOk();

        $data = $this->assertSuccessJsonResponse($response)['data'];

        $this->assertArrayHasKey('comments', $data);

        $dataComments = $data['comments'];
        $this->assertSameSize($comments, $dataComments);

        $dataComment = current($dataComments);
        $this->assertEquals($comments->last()->id, $dataComment['id']);
        $this->assertEquals($post->id, $dataComment['post_id']);
    }

    /** @test */
    public function post_posts_comments_non_signin_user(): void
    {
        $post = Post::factory()->create();
        $response = $this->postJson($this->uri("/comments/posts/{$post->id}"))
            ->assertUnauthorized();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function post_posts_comments_required_parameters(): void
    {
        $this->createSigninUser();
        $post = Post::factory()->create();
        $response = $this->postJson($this->uri("/comments/posts/{$post->id}"))
            ->assertUnprocessable();

        $errors = $this->assertErrorJsonResponse($response)['errors'];
        $this->assertCount(1, $errors);
        $this->assertArrayHasKey('body', $errors);
    }

    /** @test */
    public function post_posts_comments_invalid_post(): void
    {
        $this->createSigninUser();
        $this->postJson($this->uri('/posts/comments/123'))
            ->assertNotFound();
    }

    /** @test */
    public function post_posts_comments(): void
    {
        $this->createSigninUser();
        $post = Post::factory()->create();
        $param = ['body' => $this->faker->paragraph];
        $response = $this->postJson($this->uri("/comments/posts/{$post->id}"), $param)
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
