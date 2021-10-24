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
    public function get_comments_single_post(): void
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

        $this->assertArrayHasKey('user', $dataComment);
        $dataCommentUser = $dataComment['user'];
        $this->assertEquals($comments->last()->user->id, $dataCommentUser['id']);
    }

    /** @test */
    public function post_comments_single_post_non_signin_user(): void
    {
        $post = Post::factory()->create();
        $response = $this->postJson($this->uri("/comments/posts/{$post->id}"))
            ->assertUnauthorized();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function post_comments_single_post_required_parameters(): void
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
    public function post_comments_single_post_invalid_post(): void
    {
        $this->createSigninUser();
        $this->postJson($this->uri('/posts/comments/123'))
            ->assertNotFound();
    }

    /** @test */
    public function post_comments_single_post(): void
    {
        $user = $this->createSigninUser();
        $post = Post::factory()->create();
        $param = ['body' => $this->faker->paragraph];
        $response = $this->postJson($this->uri("/comments/posts/{$post->id}"), $param)
            ->assertOk();

        $data = $this->assertSuccessJsonResponse($response)['data'];

        $this->assertArrayHasKey('comment', $data);
        $dataComment = $data['comment'];
        $this->assertEquals($param['body'], $dataComment['body']);

        $this->assertCount(1, $post->comments);
        $this->assertTrue($user->is($post->comments->first()->user));
    }
}
