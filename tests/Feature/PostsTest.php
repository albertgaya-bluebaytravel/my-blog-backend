<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Enums\StatusCodeEnum;
use App\Models\Post;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

/** @group PostsTest */
class PostsTest extends TestCase
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
}
