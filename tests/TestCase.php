<?php

namespace Tests;

use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Testing\TestResponse;
use Illuminate\Testing\AssertableJsonString;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    private $urlPrefix = '/api';

    protected function uri(string $uri): string
    {
        return prefix_url($uri);
    }

    protected function createSigninUser(): User
    {
        $user = User::factory()->verified()->create();
        Sanctum::actingAs($user);
        return $user;
    }

    protected function assertSuccessJsonResponse(TestResponse $response): AssertableJsonString
    {
        return $response->assertJsonStructure(['data', 'message'])->decodeResponseJson();
    }

    protected function assertErrorJsonResponse(TestResponse $response): AssertableJsonString
    {
        return $response->assertJsonStructure(['errors', 'message'])->decodeResponseJson();
    }
}
