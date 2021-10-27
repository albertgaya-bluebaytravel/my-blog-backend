<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

/** @group PostsCommentsReplyTest */
class PostsCommentsReplyTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function post_single_post_single_comment_reply_non_signin_user(): void
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post]);
        $response = $this->postJson($this->uri("/posts/{$post->id}/comments/{$comment->id}/reply"))
            ->assertUnauthorized();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function post_single_post_single_comment_reply_required_parameters(): void
    {
        $this->createSigninUser();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post]);
        $response = $this->postJson($this->uri("/posts/{$post->id}/comments/{$comment->id}/reply"))
            ->assertUnprocessable();

        $errors = $this->assertErrorJsonResponse($response)['errors'];
        $this->assertCount(1, $errors);
        $this->assertArrayHasKey('body', $errors);
    }

    /** @test */
    public function post_single_post_single_comment_reply_invalid_post(): void
    {
        $this->createSigninUser();
        $comment = Comment::factory()->create();
        $this->postJson($this->uri("/posts/123/comments/{$comment->id}/reply"))
            ->assertNotFound();
    }

    /** @test */
    public function post_single_post_single_comment_reply_invalid_comment(): void
    {
        $this->createSigninUser();
        $post = Post::factory()->create();
        $this->postJson($this->uri("/posts/{$post->id}/comments/123/reply"))
            ->assertNotFound();
    }

    /** @test */
    public function post_single_post_single_comment_reply_non_related_post_and_comment(): void
    {
        $this->createSigninUser();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create();
        $response = $this->postJson($this->uri("/posts/{$post->id}/comments/{$comment->id}/reply"))
            ->assertNotFound();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function post_single_post_single_comment_reply(): void
    {
        $user = $this->createSigninUser();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post]);

        $param = ['body' => $this->faker->paragraph()];

        $response = $this->postJson($this->uri("/posts/{$post->id}/comments/{$comment->id}/reply"), $param)
            ->assertOk();

        $data = $this->assertSuccessJsonResponse($response)['data'];

        $this->assertArrayHasKey('comment', $data);
        $dataComment = $data['comment'];
        $this->assertEquals($comment->id, $dataComment['parent']['id']);
        $this->assertEquals($user->id, $dataComment['user']['id']);
    }
}
