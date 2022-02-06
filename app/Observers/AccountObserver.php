<?php

namespace App\Observers;

use App\Models\Account;
use App\Notifications\AccountCreated;

class AccountObserver
{
    /**
     * Handle the Account "creating" event.
     *
     * @param  \App\Models\Account  $account
     * @return void
     */
    public function creating(Account $account)
    {
        $account->number = generateAccountNumber();
    }

    /**
     * Handle the Account "created" event.
     *
     * @param  \App\Models\Account  $account
     * @return void
     */
    public function created(Account $account)
    {
        $account->user->notify(new AccountCreated($account));
    }
}
