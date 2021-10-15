<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use App\Models\Comment;
use Laravel\Sanctum\Sanctum;
use App\Enums\StatusCodeEnum;
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

        $this->assertEquals($param['title'], $post['title']);
        $this->assertEquals($param['body'], $post['body']);

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
    public function it_can_create_a_comment_to_a_post(): void
    {
        $user = User::factory()->verified()->create();
        Sanctum::actingAs($user);

        $post = Post::factory()->create();
        $param = ['body' => $this->faker->paragraph];
        $response = $this->postJson($this->uri('/posts/' . $post->id . '/comments'), $param)
            ->assertStatus(StatusCodeEnum::OK);

        $this->assertDatabaseCount(Comment::class, 1);
        $this->assertNotNull(Comment::first()->post);
        $this->assertCount(1, $post->refresh()->comments);

        $data = $this->assertSuccessJsonResponse($response)['data'];
        $this->assertArrayHasKey('post', $data);
        $this->assertArrayHasKey('comment', $data);
    }
}
