<?php

namespace Tests\Feature;

use App\Models\Type;
use App\Models\User;
use Database\Factories\AccountFactory;
use Faker\Factory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AccountTest extends TestCase
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
     * Get faker data
     *
     * @return Factory;;
     */

    function faker($property)
    {
        $faker = Factory::create();
        return $property ? $faker->{$property} : $faker;
    }


    /**
     * Generate Account.
     *
     * @return void
     */
    public function test_account_can_be_generated()
    {
        $authResponse = $this->getAuthResponse();

        $response = $this->getNewAccountResponse($authResponse);

        $response->assertOk();
    }

    /**
     * @return void
     */
    public function test_new_account_has_an_initial_balance()
    {
        $authResponse = $this->getAuthResponse();

        $response = $this->getNewAccountResponse($authResponse);

        $response->assertOk();
        $this->assertEquals($response->json('data.account.balance'), 2000);
    }

    /**
     * @return void
     */
    public function test_can_retrieve_account_balance()
    {
        $authResponse = $this->getAuthResponse();

        $account = $this->getNewAccountResponse($authResponse);

        $response = $this->withHeaders(array_merge($this->headers(), [
            'Authorization' => "Bearer {$authResponse->json('data.token')}"
        ]))->withoutExceptionHandling()
            ->getJson("/api/v1/accounts/{$account->json('data.account.ref')}/balance");

        $response->assertOk();
        $response->assertSee($response->json('data.account.balance'));
    }

    /**
     * @return void
     */
    public function test_can_retrieve_account_transactions()
    {
        $authResponse = $this->getAuthResponse();

        $account = $this->getNewAccountResponse($authResponse);

        $response = $this->withHeaders(array_merge($this->headers(), [
            'Authorization' => "Bearer {$authResponse->json('data.token')}"
        ]))->withoutExceptionHandling()
            ->getJson("/api/v1/accounts/{$account->json('data.account.ref')}/transactions");

        $response->assertOk();
        $response->assertSee($response->json('data.account.transactions'));
    }

    /**
     * @return void
     */
    public function test_can_transfer_between_accounts()
    {
        $authResponse = $this->getAuthResponse();

        $creditAccount = $this->getNewAccountResponse($authResponse);
        $debitAccount = $this->getNewAccountResponse($authResponse, $creditAccount->json('data.account.type.name'), 5000);
        $amount = 2000;

        $response = $this->withHeaders(array_merge($this->headers(), [
            'Authorization' => "Bearer {$authResponse->json('data.token')}"
        ]))->withoutExceptionHandling()
            ->postJson('/api/v1/accounts/transfer', [
                'credit' => $creditAccount->json('data.account.number'),
                'debit' => $debitAccount->json('data.account.number'),
                'amount' => $amount
            ]);

        $response->assertOk();

        $this->assertEquals($response->json('data.credited.balance'), $creditAccount->json('data.account.balance') + $amount);
        $this->assertEquals($response->json('data.debited.balance'), $debitAccount->json('data.account.balance') - $amount);

    }

    /**
     * @return \Illuminate\Testing\TestResponse
     */
    protected function getAuthResponse(): \Illuminate\Testing\TestResponse
    {
        $user = User::firstWhere('email', 'mac.ryan@email.com');
        return $this->withHeaders($this->headers())
            ->withoutExceptionHandling()
            ->postJson('/api/v1/auth/login', [
                    'email' => $user->email,
                    'password' => env('ADMIN_PASSWORD')
                ]
            );
    }

    /**
     * @param \Illuminate\Testing\TestResponse $authResponse
     * @return \Illuminate\Testing\TestResponse
     */
    protected function getNewAccountResponse(\Illuminate\Testing\TestResponse $authResponse, ?string $account=null, int $amount = 2000): \Illuminate\Testing\TestResponse
    {
        return $this->withHeaders(array_merge($this->headers(), [
            'Authorization' => "Bearer {$authResponse->json('data.token')}"
        ]))->withoutExceptionHandling()
            ->postJson('/api/v1/account/generate', [
                'first_name' => $this->faker('firstName'),
                'last_name' => $this->faker('lastName'),
                'email' => $this->faker('safeEmail'),
                'account_type' => $account ?? Type::all()->random()->name,
                'initial_deposit' => $amount
            ]
        );
    }
}
