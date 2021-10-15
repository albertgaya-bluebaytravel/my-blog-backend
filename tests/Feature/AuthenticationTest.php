<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Enums\StatusCodeEnum;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

/** @group AuthenticationTest */
class AuthenticationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function createUser(string $password = ''): User
    {
        if (!$password) {
            $password = $this->faker->password;
        }

        $data = [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => $password,
            'password_confirmation' => $password
        ];

        $this->postJson($this->uri('/users'), $data);

        $user = User::first();

        $this->postJson($this->uri('/users/' . Crypt::encrypt($user->id) . '/verify'))
            ->assertStatus(StatusCodeEnum::OK);

        return $user;
    }

    /** @test */
    public function it_can_validate_login_required_parameters(): void
    {
        $response = $this->postJson($this->uri('/login'))->assertStatus(StatusCodeEnum::UNPROCESSABLE_ENTITY);

        $errors = $this->assertErrorJsonResponse($response)['errors'];
        $this->assertCount(2, $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('password', $errors);
    }

    /** @test */
    public function it_can_login_user_and_get_token(): void
    {
        $password = $this->faker->password;
        $user = $this->createUser($password);

        $response = $this->postJson($this->uri('/login'), [
            'email' => $user->email,
            'password' => $password
        ])->assertStatus(StatusCodeEnum::OK);

        $data = $this->assertSuccessJsonResponse($response)['data'];
        $this->assertIsString($data);
    }

    /** @test */
    public function it_can_validate_invalid_user(): void
    {
        $response = $this->postJson($this->uri('/login'), [
            'email' => $this->faker->email,
            'password' => $this->faker->password
        ])->assertStatus(StatusCodeEnum::UNPROCESSABLE_ENTITY);

        $errors = $this->assertErrorJsonResponse($response)['errors'];
        $this->assertCount(1, $errors);
        $this->assertArrayHasKey('email', $errors);
    }
}
