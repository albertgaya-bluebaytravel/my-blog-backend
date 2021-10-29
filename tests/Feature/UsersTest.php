<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\NewUserVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
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
        $this->assertNotNull($user->email_verification_token);

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

        $response = $this->getJson($this->uri("/users/{$user->id}/verify/{$user->email_verification_token}"))
            ->assertUnprocessable();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function post_users_verifiy_invalid_token(): void
    {
        $user = User::factory()->unverified()->create();

        $response = $this->getJson($this->uri("/users/{$user->id}/verify/123"))
            ->assertUnprocessable();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function post_users_verify(): void
    {
        $user = User::factory()->unverified()->create();

        $this->getJson($this->uri("/users/{$user->id}/verify/{$user->email_verification_token}"))
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
    public function post_users_login_unverified_user(): void
    {
        $password = $this->faker->password;
        $user = User::factory()->unverified()->create(['password' => $password]);

        $response = $this->postJson($this->uri('/users/login'), [
            'email' => $user->email,
            'password' => $password
        ])
            ->assertUnprocessable();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function post_users_login(): void
    {
        $password = $this->faker->password;
        $user = User::factory()->verified()->create(['password' => $password]);

        $this->postJson($this->uri('/users/login'), [
            'email' => $user->email,
            'password' => $password
        ])->assertOk();

        $this->assertTrue($user->is(Auth::user()));
    }

    /** @test */
    public function get_users_auth_non_login_user(): void
    {
        $response = $this->getJson('/users/auth')
            ->assertNotFound();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function get_users_auth(): void
    {
        $user = $this->createSigninUser();
        $response = $this->getJson($this->uri('/users/auth'))
            ->assertOk();

        $data = $this->assertSuccessJsonResponse($response)['data'];

        $this->assertArrayHasKey('user', $data);
        $dataUser = $data['user'];
        $this->assertEquals($user->id, $dataUser['id']);
    }

    /** @test */
    public function patch_users_profile_non_signin_user(): void
    {
        $user = User::factory()->create();
        $response = $this->patchJson($this->uri("/users/{$user->id}/profile"))
            ->assertUnauthorized();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function patch_users_profile_non_owner_user(): void
    {
        $this->createSigninUser();
        $user = User::factory()->create();
        $response = $this->patchJson($this->uri("/users/{$user->id}/profile"))
            ->assertForbidden();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function patch_users_profile_validate_email(): void
    {
        $user = $this->createSigninUser();

        $response = $this->patchJson($this->uri("/users/{$user->id}/profile"), [
            'email' => User::factory()->create()->email
        ])
            ->assertUnprocessable();
        $this->assertErrorJsonResponse($response);
    }

    /** @test */
    public function patch_users_profile(): void
    {
        $user = User::factory()->verified()->create(['password' => '123']);
        Sanctum::actingAs($user);

        $param = $this->data() + ['password_change' => true];
        $response = $this->patchJson($this->uri("users/{$user->id}/profile"), $param)->assertOk();

        $data = $this->assertSuccessJsonResponse($response)['data'];

        $this->assertArrayHasKey('user', $data);
        $dataUser = $data['user'];
        $user->refresh();
        $this->assertEquals($user->name, $dataUser['name']);
        $this->assertEquals($user->email, $dataUser['email']);
        $this->assertTrue(Hash::check('123', $user->password));
    }
}
