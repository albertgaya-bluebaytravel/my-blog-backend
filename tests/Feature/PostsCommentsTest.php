<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

/** @group PostsCommentsTest */
class PostsCommentsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function get_single_post_comments(): void
    {
        $post = Post::factory()->create();
        $comments = Comment::factory()->count(10)->create(['post_id' => $post]);

        $response = $this->getJson($this->uri("/posts/{$post->id}/comments"))
            ->assertOk();

        $data = $this->assertSuccessJsonResponse($response)['data'];

        $this->assertArrayHasKey('comments', $data);

        $dataComments = $data['comments'];
        $this->assertSameSize($comments, $dataComments);

        $dataComment = current($dataComments);
        $this->assertEquals($comments->last()->id, $dataComment['id']);
        $this->assertEquals($post->id, $dataComment['post_id']);

        $this->assertArrayHasKey('user', $dataComment);
        $dataCommentUser = $dataComment['user'];
        $this->assertEquals($comments->last()->user->id, $dataCommentUser['id']);
    }

    /** @test */
    public function post_single_post_comments_non_signin_user(): void
    {
        $post = Post::factory()->create();
        $response = $this->postJson($this->uri("/posts/{$post->id}/comments"))
            ->assertUnauthorized();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function post_single_post_comments_required_parameters(): void
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
    public function post_single_post_comments_invalid_post(): void
    {
        $this->createSigninUser();
        $this->postJson($this->uri('/comments/123/posts'))
            ->assertNotFound();
    }

    /** @test */
    public function post_single_post_comments(): void
    {
        $user = $this->createSigninUser();
        $post = Post::factory()->create();
        $param = ['body' => $this->faker->paragraph()];
        $response = $this->postJson($this->uri("/posts/{$post->id}/comments"), $param)
            ->assertOk();

        $data = $this->assertSuccessJsonResponse($response)['data'];

        $this->assertArrayHasKey('comment', $data);
        $dataComment = $data['comment'];
        $this->assertEquals($param['body'], $dataComment['body']);

        $this->assertCount(1, $post->comments);
        $this->assertTrue($user->is($post->comments->first()->user));
    }

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
        $this->postJson($this->uri("/posts/{$post->id}/comments/{$comment->id}/reply"))
            ->assertNotFound();
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
