<?php

namespace Tests\Feature\Users;

use App\Enums\StatusCodeEnum;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

/** @group UserVerificationTest */
class UserVerificationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /** @test */
    public function it_can_verify_user(): void
    {
        $this->postJson($this->uri('/users'), [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => '123',
            'password_confirmation' => '123'
        ]);

        $user = User::first();

        $this->postJson($this->uri('/users/' . Crypt::encrypt($user->id) . '/verify'))
            ->assertStatus(StatusCodeEnum::OK);

        $user->refresh();

        $this->assertNotNull($user->email_verified_at);
        $this->assertEquals(1, $user->is_active);
    }
}
