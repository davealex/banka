<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * Security check.
     *
     * @return void
     */
    public function test_user_login_is_secure()
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'daveabiola@gmail.com',
            'password' => 'bhbbnlklkmk'
        ]);

        $response->assertStatus(401);
    }
}
