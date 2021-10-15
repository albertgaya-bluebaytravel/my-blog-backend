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

/** @group PostTest */
class PostTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_can_create_new_post(): void
    {
        $user = User::factory()->verified()->create();
        Sanctum::actingAs($user);

        $param = [
            'title' => $this->faker->title,
            'body' => $this->faker->paragraph,
        ];

        $response = $this->postJson($this->uri('/posts'), $param)->assertStatus(StatusCodeEnum::OK);

        $this->assertDatabaseCount(Post::class, 1);

        $post = Post::first();
        $this->assertTrue($post->user->is($user));

        $this->assertCount(1, $user->posts);

        $data = $this->assertSuccessJsonResponse($response)['data'];
        $this->assertArrayHasKey('post', $data);

        $post = $data['post'];
        $this->assertEquals($param['title'], $post['title']);
        $this->assertEquals($param['body'], $post['body']);
        $this->assertEquals($user->id, $post['user_id']);
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
