<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Comment;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

/** @group CommentsTest */
class CommentsTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function patch_single_comment_non_singin_user(): void
    {
        $comment = Comment::factory()->create();
        $response = $this->patchJson($this->uri("/comments/{$comment->id}"))
            ->assertUnauthorized();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function patch_single_comment_non_owner_user(): void
    {
        $this->createSigninUser();
        $comment = Comment::factory()->create();
        $response = $this->patchJson($this->uri("/comments/{$comment->id}"))
            ->assertForbidden();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function patch_single_comment(): void
    {
        $user = $this->createSigninUser();
        $comment = Comment::factory()->create(['user_id' => $user]);

        $param = ['body' => $this->faker->sentence()];

        $response = $this->patchJson($this->uri("/comments/{$comment->id}"), $param)
            ->assertOk();

        $data = $this->assertSuccessJsonResponse($response)['data'];

        $this->assertArrayHasKey('comment', $data);
        $dataComment = $data['comment'];
        $this->assertEquals($param['body'], $dataComment['body']);
    }

    /** @test */
    public function delete_single_comment_non_signin_user(): void
    {
        $comment = Comment::factory()->create();
        $response = $this->deleteJson($this->uri("/comments/{$comment->id}"))
            ->assertUnauthorized();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function delete_single_comment_non_owner_user(): void
    {
        $this->createSigninUser();
        $comment = Comment::factory()->create();
        $response = $this->deleteJson($this->uri("/comments/{$comment->id}"))
            ->assertForbidden();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function delete_single_comment(): void
    {
        $user = $this->createSigninUser();
        $comment = Comment::factory()->create(['user_id' => $user]);

        $response = $this->deleteJson($this->uri("/comments/{$comment->id}"))
            ->assertOk();

        $this->assertSuccessJsonResponse($response);
        $this->assertNull($comment->fresh());
    }
}
