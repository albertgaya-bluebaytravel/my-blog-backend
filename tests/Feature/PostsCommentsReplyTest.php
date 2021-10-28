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
    public function post_single_post_single_comment_replies_non_signin_user(): void
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post]);
        $response = $this->postJson($this->uri("/posts/{$post->id}/comments/{$comment->id}/replies"))
            ->assertUnauthorized();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function post_single_post_single_comment_replies_required_parameters(): void
    {
        $this->createSigninUser();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post]);
        $response = $this->postJson($this->uri("/posts/{$post->id}/comments/{$comment->id}/replies"))
            ->assertUnprocessable();

        $errors = $this->assertErrorJsonResponse($response)['errors'];
        $this->assertCount(1, $errors);
        $this->assertArrayHasKey('body', $errors);
    }

    /** @test */
    public function post_single_post_single_comment_replies_invalid_post(): void
    {
        $this->createSigninUser();
        $comment = Comment::factory()->create();
        $this->postJson($this->uri("/posts/123/comments/{$comment->id}/replies"))
            ->assertNotFound();
    }

    /** @test */
    public function post_single_post_single_comment_replies_invalid_comment(): void
    {
        $this->createSigninUser();
        $post = Post::factory()->create();
        $this->postJson($this->uri("/posts/{$post->id}/comments/123/replies"))
            ->assertNotFound();
    }

    /** @test */
    public function post_single_post_single_comment_replies_non_related_post_and_comment(): void
    {
        $this->createSigninUser();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create();
        $response = $this->postJson($this->uri("/posts/{$post->id}/comments/{$comment->id}/replies"))
            ->assertNotFound();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function post_single_post_single_comment_replies(): void
    {
        $user = $this->createSigninUser();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post]);

        $param = ['body' => $this->faker->paragraph()];

        $response = $this->postJson($this->uri("/posts/{$post->id}/comments/{$comment->id}/replies"), $param)
            ->assertOk();

        $data = $this->assertSuccessJsonResponse($response)['data'];

        $this->assertArrayHasKey('comment', $data);
        $dataComment = $data['comment'];
        $this->assertEquals($comment->id, $dataComment['parent']['id']);
        $this->assertEquals($user->id, $dataComment['user']['id']);
    }

    /** @test */
    public function patch_single_post_single_comment_single_reply_non_signin_user(): void
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post]);
        $reply = Comment::factory()->create(['post_id' => $post, 'parent_id' => $comment]);
        $response = $this->patchJson($this->uri("/posts/{$post->id}/comments/{$comment->id}/replies/{$reply->id}"))
            ->assertUnauthorized();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function patch_single_post_single_comment_single_reply_non_owner_user(): void
    {
        $this->createSigninUser();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post]);
        $reply = Comment::factory()->create(['post_id' => $post, 'parent_id' => $comment]);
        $response = $this->patchJson($this->uri("/posts/{$post->id}/comments/{$comment->id}/replies/{$reply->id}"))
            ->assertForbidden();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function patch_single_post_single_comment_single_reply_required_parameters(): void
    {
        $user = $this->createSigninUser();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post]);
        $reply = Comment::factory()->create(['post_id' => $post, 'parent_id' => $comment, 'user_id' => $user]);
        $response = $this->patchJson($this->uri("/posts/{$post->id}/comments/{$comment->id}/replies/{$reply->id}"))
            ->assertUnprocessable();

        $errors = $this->assertErrorJsonResponse($response)['errors'];
        $this->assertCount(1, $errors);
        $this->assertArrayHasKey('body', $errors);
    }

    /** @test */
    public function patch_single_post_single_comment_single_reply_invalid_post(): void
    {
        $user = $this->createSigninUser();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post]);
        $reply = Comment::factory()->create(['post_id' => $post, 'parent_id' => $comment, 'user_id' => $user]);
        $this->patchJson($this->uri("/posts/123/comments/{$comment->id}/replies/{$reply->id}"))
            ->assertNotFound();
    }

    /** @test */
    public function patch_single_post_single_comment_single_reply_invalid_comment(): void
    {
        $user = $this->createSigninUser();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post]);
        $reply = Comment::factory()->create(['post_id' => $post, 'parent_id' => $comment, 'user_id' => $user]);
        $this->patchJson($this->uri("/posts/{$post->id}/comments/123/replies/{$reply->id}"))
            ->assertNotFound();
    }

    /** @test */
    public function patch_single_post_single_comment_single_reply_invalid_reply(): void
    {
        $this->createSigninUser();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post]);
        $this->patchJson($this->uri("/posts/{$post->id}/comments/{$comment->id}/replies/123"))
            ->assertNotFound();
    }

    /** @test */
    public function patch_single_post_single_comment_single_reply_non_related_post_and_comment(): void
    {
        $user = $this->createSigninUser();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create();
        $reply = Comment::factory()->create(['post_id' => $post, 'parent_id' => $comment, 'user_id' => $user]);
        $response = $this->patchJson($this->uri("/posts/{$post->id}/comments/{$comment->id}/replies/{$reply->id}"))
            ->assertNotFound();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function patch_single_post_single_comment_single_reply_non_related_comment_and_reply(): void
    {
        $user = $this->createSigninUser();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post]);
        $reply = Comment::factory()->create(['post_id' => $post, 'parent_id' => Comment::factory(), 'user_id' => $user]);
        $response = $this->patchJson($this->uri("/posts/{$post->id}/comments/{$comment->id}/replies/{$reply->id}"))
            ->assertNotFound();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function patch_single_post_single_comment_single_reply(): void
    {
        $user = $this->createSigninUser();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['post_id' => $post]);
        $reply = Comment::factory()->create(['post_id' => $post, 'parent_id' => $comment, 'user_id' => $user]);

        $param = ['body' => $this->faker->paragraph()];

        $response = $this->patchJson($this->uri("/posts/{$post->id}/comments/{$comment->id}/replies/{$reply->id}"), $param)
            ->assertOk();

        $data = $this->assertSuccessJsonResponse($response)['data'];

        $this->assertArrayHasKey('comment', $data);
        $dataComment = $data['comment'];
        $this->assertEquals($post->id, $dataComment['post_id']);
        $this->assertEquals($comment->id, $dataComment['parent_id']);
    }
}
