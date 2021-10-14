<?php

namespace Tests\Feature\Users;

use App\Enums\StatusCodeEnum;
use App\Mail\NewUserEmailVerification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/** @group UsersTest */
class UsersTest extends TestCase
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
    public function it_validate_user_registration(): void
    {
        $response = $this->postJson($this->uri('/users'))
            ->assertStatus(StatusCodeEnum::UNPROCESSABLE_ENTITY);
        $errors = $this->assertErrorJsonResponse($response)['errors'];
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
        Mail::fake();

        $this->postJson($this->uri('/users'), $this->data())
            ->assertStatus(StatusCodeEnum::OK);

        Mail::assertSent(function (NewUserEmailVerification $mail) {
            return $mail->to[0]['email'] = User::first()->email;
        });
    }
}
