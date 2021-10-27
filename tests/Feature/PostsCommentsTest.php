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
        $this->postJson($this->uri("/posts/123/comments"))
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
    public function patch_single_post_single_comment_non_singin_user(): void
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()->create();
        $response = $this->patchJson($this->uri("/posts/{$post->id}/comments/{$comment->id}"))
            ->assertUnauthorized();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function patch_singel_post_single_comment_non_owner_user(): void
    {
        $this->createSigninUser();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create();
        $response = $this->patchJson($this->uri("/posts/{$post->id}/comments/{$comment->id}"))
            ->assertForbidden();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function patch_single_post_single_comment_non_related_post_and_comment(): void
    {
        $user = $this->createSigninUser();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user]);
        $response = $this->patchJson($this->uri("/posts/{$post->id}/comments/{$comment->id}"))
            ->assertNotFound();
        $this->assertErrorJsonResponse($response);
    }
    /** @test */
    public function patch_single_post_single_comment(): void
    {
        $user = $this->createSigninUser();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create([
            'post_id' => $post,
            'user_id' => $user
        ]);

        $param = ['body' => $this->faker->sentence()];

        $response = $this->patchJson($this->uri("/posts/{$post->id}/comments/{$comment->id}"), $param)
            ->assertOk();

        $data = $this->assertSuccessJsonResponse($response)['data'];

        $this->assertArrayHasKey('comment', $data);
        $dataComment = $data['comment'];
        $this->assertEquals($param['body'], $dataComment['body']);
    }

    /** @test */
    public function delete_single_post_single_comment_non_signin_user(): void
    {
        $post = Post::factory()->create();
        $comment = Comment::factory()->create();
        $response = $this->deleteJson($this->uri("/posts/{$post->id}/comments/{$comment->id}"))
            ->assertUnauthorized();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function delete_single_post_single_comment_non_owner_user(): void
    {
        $this->createSigninUser();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create();
        $response = $this->deleteJson($this->uri("/posts/{$post->id}/comments/{$comment->id}"))
            ->assertForbidden();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function delete_single_post_single_comment_non_related_post_and_comment(): void
    {
        $user = $this->createSigninUser();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create(['user_id' => $user]);
        $response = $this->deleteJson($this->uri("/posts/{$post->id}/comments/{$comment->id}"))
            ->assertNotFound();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function delete_single_post_single_comment(): void
    {
        $user = $this->createSigninUser();
        $post = Post::factory()->create();
        $comment = Comment::factory()->create([
            'post_id' => $post,
            'user_id' => $user
        ]);

        $response = $this->deleteJson($this->uri("/posts/{$post->id}/comments/{$comment->id}"))
            ->assertOk();

        $this->assertSuccessJsonResponse($response);
        $this->assertNull($comment->fresh());
    }
}
