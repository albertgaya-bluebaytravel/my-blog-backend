<?php

namespace Tests\Feature\Users;

use App\Enums\StatusCodeEnum;
use App\Models\User;
use App\Notifications\NewUserVerification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/** @group UserRegistrationTest */
class UserRegistrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    private function data(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => '123',
            'password_confirmation' => '123'
        ];
    }

    /** @test */
    public function it_validate_user_registration_required_parameters(): void
    {
        $response = $this->postJson($this->uri('/users'))
            ->assertStatus(StatusCodeEnum::UNPROCESSABLE_ENTITY);
        $errors = $this->assertErrorJsonResponse($response)['errors'];
        $this->assertCount(3, $errors);
        $this->assertArrayHasKey('name', $errors);
        $this->assertArrayHasKey('email', $errors);
        $this->assertArrayHasKey('password', $errors);
        $this->assertDatabaseCount(User::class, 0);
    }

    /** @test */
    public function it_can_register_a_user(): void
    {
        $response = $this->postJson($this->uri('/users'), $this->data())
            ->assertStatus(StatusCodeEnum::OK);
        $this->assertSuccessJsonResponse($response);
        $this->assertDatabaseCount(User::class, 1);
    }

    /** @test */
    public function it_validate_user_registration_unique_email(): void
    {
        $this->postJson($this->uri('/users'), $this->data());

        $user = User::first();

        $response = $this->postJson($this->uri('/users'), ['email' => $user->email] + $this->data());
        $errors = $this->assertErrorJsonResponse($response)['errors'];
        $this->assertCount(1, $errors);
        $this->assertArrayHasKey('email', $errors);
    }

    /** @test */
    public function it_should_set_new_user_email_not_verified_and_inactive(): void
    {
        $this->postJson($this->uri('/users'), $this->data());

        $user = User::first();
        $this->assertNull($user->email_verified_at);
        $this->assertEquals(0, $user->is_active);
    }

    /** @test */
    public function it_should_send_an_email_verification(): void
    {
        Notification::fake();
        $this->postJson($this->uri('/users'), $this->data());
        Notification::assertSentTo(User::first(), NewUserVerification::class);
    }
}
