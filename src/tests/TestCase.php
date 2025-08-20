<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Laravel\Sanctum\Sanctum;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Helper do logowania użytkownika w testach API
     */
    protected function authenticateUser($user = null)
    {
        $user = $user ?? \App\Models\User::factory()->create();
        Sanctum::actingAs($user, ['*']);
        return $user;
    }


    protected function disableExceptionHandling(): void
    {
        $this->withoutExceptionHandling();
    }

    protected function enableExceptionHandling(): void
    {
        $this->withExceptionHandling();
    }
}
