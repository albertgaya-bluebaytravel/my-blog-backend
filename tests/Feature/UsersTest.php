<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\NewUserVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

use function PHPUnit\Framework\assertNotEmpty;

/** @group UsersTest */
class UsersTest extends TestCase
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
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => '123',
            'password_confirmation' => '123'
        ];
    }

    /** @test */
    public function post_users_register_required_parameters(): void
    {
        $response = $this->postJson($this->uri('/users/register'))
            ->assertUnprocessable();
        $errors = $this->assertErrorJsonResponse($response)['errors'];
        $this->assertCount(3, $errors);
        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('password', $errors);
        $this->assertDatabaseCount(User::class, 0);
    }

    /** @test */
    public function post_users_register_validate_email(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson($this->uri('/users/register'), $this->data(['email' => $user->email]))
            ->assertUnprocessable();
        $errors = $this->assertErrorJsonResponse($response)['errors'];
        $this->assertCount(1, $errors);
        $this->assertArrayHasKey('email', $errors);
    }

    /** @test */
    public function post_users_register(): void
    {
        Notification::fake();

        $param = $this->data();

        $response = $this->postJson($this->uri('/users/register'), $param)
            ->assertOk();

        $data = $this->assertSuccessJsonResponse($response)['data'];

        $this->assertArrayHasKey('user', $data);
        $dataUser = $data['user'];

        $user = User::find($dataUser['id']);
        $this->assertNotNull($user);

        $this->assertEquals($param['name'], $dataUser['name']);
        $this->assertEquals($param['email'], $dataUser['email']);
        $this->assertTrue(Hash::check($param['password'], $user->password));

        $this->assertNull($user->email_verified_at);
        $this->assertEquals(0, $user->is_active);

        Notification::assertSentTo($user, NewUserVerification::class);
    }

    /** @test */
    public function post_users_verify_required_parameters(): void
    {
        $response = $this->getJson($this->uri('/users/123/verify'))
            ->assertNotFound();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function post_users_verify_verified_user(): void
    {
        $user = User::factory()->verified()->create();

        $response = $this->getJson($this->uri("/users/{$user->id}/verify"))
            ->assertUnprocessable();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function post_users_verify(): void
    {
        $user = User::factory()->unverified()->create();

        $this->getJson($this->uri("/users/{$user->id}/verify"))
            ->assertOk();

        $user->refresh();

        $this->assertNotNull($user->email_verified_at);
        $this->assertEquals(1, $user->is_active);
    }

    /** @test */
    public function post_users_login_required_parameters(): void
    {
        $response = $this->postJson($this->uri('/users/login'))
            ->assertUnprocessable();

        $errors = $this->assertErrorJsonResponse($response)['errors'];
        $this->assertCount(2, $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('password', $errors);
    }

    /** @test */
    public function post_users_login_invalid_user(): void
    {
        $response = $this->postJson($this->uri('/users/login'), [
            'email' => $this->faker->email,
            'password' => $this->faker->password
        ])->assertUnprocessable();
        $this->assertErrorJsonResponse($response)['errors'];
    }

    /** @test */
    public function post_users_login(): void
    {
        $password = $this->faker->password;
        $user = User::factory()->verified()->create(['password' => $password]);

        $response = $this->postJson($this->uri('/users/login'), [
            'email' => $user->email,
            'password' => $password
        ])->assertOk();

        $data = $this->assertSuccessJsonResponse($response)['data'];
        $this->assertArrayHasKey('token', $data);
        $this->assertNotEmpty($data['token']);
    }
}
