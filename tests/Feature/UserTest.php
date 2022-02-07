<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * @return array
     */
    public function headers(): array
    {
        return [
            'X-Authorization' => DB::table('api_keys')->first()->key,
        ];
    }

    /**
     * Security check.
     *
     * @return void
     */
    public function test_user_login_is_secure()
    {
        $response = $this->withoutExceptionHandling()->postJson('/api/v1/auth/login', [
            'email' => 'daveabiola@gmail.com',
            'password' => 'bhbbnlklkmk'
        ]);

        $response->assertStatus(401);
    }

    /**
     * @return void
     */
    public function test_user_can_login_with_correct_credentials()
    {
        $user = $this->getModel();
        $response = $this->getAuthenticatedUser($user);

        $response->assertOk();
        $this->assertEquals($response->json('data.user.first_name'), $user->first_name);
    }

    /**
     * @return void
     */
    public function test_user_has_a_profile()
    {
        $user = $this->getModel();
        $response = $this->getAuthenticatedUser($user);

        $response->assertOk();
        $response->assertSee($response->json('data.user.profile.id'));
    }

    /**
     * @return void
     */
    public function test_user_has_a_role()
    {
        $user = $this->getModel();
        $response = $this->getAuthenticatedUser($user);

        $response->assertOk();
        $response->assertSee($response->json('data.user.role.id'));
    }

    /**
     * @return void
     */
    public function test_user_has_a_default_avatar()
    {
        $user = $this->getModel();
        $response = $this->getAuthenticatedUser($user);

        $response->assertOk();
        $response->assertSee($response->json('data.user.profile.upload.id'));
    }

    /**
     * @return void
     */
    public function test_user_can_logout()
    {
        $user = $this->getModel();
        $this->getAuthenticatedUser($user);

        $response = $this->withHeaders($this->headers())->withoutExceptionHandling()
            ->postJson('/api/v1/auth/logout');

        $response->assertOk();
        $response->assertSee($response->json('message'));
    }

    /**
     * @param Model $user
     * @return \Illuminate\Testing\TestResponse
     */
    protected function getAuthenticatedUser(Model $user): \Illuminate\Testing\TestResponse
    {
        return $this->withHeaders($this->headers())->withoutExceptionHandling()
            ->postJson('/api/v1/auth/login', [
                'email' => $user->email,
                'password' => '$2y$10$92IXUNp'
            ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model
     */
    protected function getModel()
    {
        return User::factory()->create();
    }
}
