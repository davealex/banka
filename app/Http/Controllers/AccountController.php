<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountTransferRequest;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Resources\AccountResource;
use App\Http\Resources\TransactionResource;
use App\Models\Account;
use App\Models\Type;
use App\Models\User;
use App\Services\BankingService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class AccountController extends Controller
{
    use ApiResponse;

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreAccountRequest  $request
     * @return JsonResponse
     */
    public function generate(StoreAccountRequest $request): JsonResponse
    {
        $user = User::createNewUser($request->validated());
        $type = Type::whereName($request['account_type'])->first();
        $amount = $request['initial_deposit'];

        return $this->success([
            'account' => new AccountResource(
                $request->user()
                ->createCustomerAccount($type, $user, $amount)
            )
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Account  $account
     * @return JsonResponse
     */
    public function balance(Account $account): JsonResponse
    {
        return $this->success([
            'account_name' => $account->user->full_name,
            'account_number' => $account->number,
            'account_type' => $account->type->name,
            'account_currency' => $account->type->currency_code,
            'account_balance' => $account->balance,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Account  $account
     * @return JsonResponse
     */
    public function transactions(Account $account): JsonResponse
    {
        return $this->success([
            'account_name' => $account->user->full_name,
            'account_number' => $account->number,
            'account_type' => $account->type->name,
            'account_currency' => $account->type->currency_code,
            'transactions' => TransactionResource::collection($account->transactions()->latest()->get())
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  AccountTransferRequest $request
     * @return JsonResponse
     */
    public function transfer(AccountTransferRequest $request): JsonResponse
    {
        try {
            (new BankingService($request->validated()))
                ->initiateTransfer();
        } catch (\Exception $exception) {
            return $this->error($exception->getMessage(), 400);
        }

        return $this->success([
            'credited' => new AccountResource(Account::firstWhere('number', $request->validated()['credit'])),
            'debited' => new AccountResource(Account::firstWhere('number', $request->validated()['debit'])),
        ]);
    }
}
