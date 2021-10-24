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
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'password' => $this->faker->password()
        ];
    }

    /** @test */
    public function store_user_data(): void
    {
        $data = $this->storeData();
        $user = $this->userService->store($data);
        $this->assertDatabaseCount(User::class, 1);
        $this->assertEquals($data['name'], $user->name);
        $this->assertEquals($data['email'], $user->email);
        $this->assertTrue(Hash::check($data['password'], $user->password));
        $this->assertNull($user->email_verified_at);
        $this->assertEquals(0, $user->is_active);
    }

    /** @test */
    public function store_user_email_notification(): void
    {
        Notification::fake();
        $user = $this->userService->store($this->storeData());
        Notification::assertSentTo($user, NewUserVerification::class);
    }
}
