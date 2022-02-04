<?php

namespace App\Http\Controllers;

use App\Http\Requests\AccountTransferRequest;
use App\Http\Requests\StoreAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
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
            'account' => $request->user()
                ->createCustomerAccount($type, $user, $amount)
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
            "{$account->type->name} / {$account->number}" => $account->balance
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
            "{$account->type->name} / {$account->number}" => $account->transactions()->latest()->get()
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
        (new BankingService($request->validated()))
            ->initiateTransfer();

        return $this->success([
            'credited' => Account::firstWhere('number', $request->validated()['credit']),
            'debited' => Account::firstWhere('number', $request->validated()['debit']),
        ]);
    }
}
