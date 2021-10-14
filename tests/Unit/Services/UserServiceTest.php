<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Support\Facades\Hash;
use App\Notifications\NewUserVerification;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

/** @group UserServiceTest */
class UserServiceTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected UserService $userService;

    protected function setup(): void
    {
        parent::setUp();

        $this->userService = app(UserService::class);
    }

    protected function storeData(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => $this->faker->password
        ];
    }

    /** @test */
    public function it_can_store_user_data(): void
    {
        $storeData = $this->storeData();
        $user = $this->userService->store($storeData);
        $this->assertDatabaseCount(User::class, 1);
        $this->assertEquals($storeData['name'], $user->name);
        $this->assertEquals($storeData['email'], $user->email);
        $this->assertTrue(Hash::check($storeData['password'], $user->password));
        $this->assertNull($user->email_verified_at);
        $this->assertEquals(0, $user->active);
    }

    /** @test */
    public function it_send_new_added_user_an_email_verification(): void
    {
        Notification::fake();
        $storeData = $this->storeData();
        $user = $this->userService->store($storeData);
        Notification::assertSentTo($user, NewUserVerification::class);
    }
}
