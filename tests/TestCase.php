<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Testing\Assert;
use Illuminate\Testing\AssertableJsonString;
use Illuminate\Testing\TestResponse;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    private $urlPrefix = '/api';

    protected function uri(string $uri): string
    {
        return $this->urlPrefix . $uri;
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
