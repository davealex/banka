<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\TransactionAlert;
use Illuminate\Database\Eloquent\Model;

class BankingService
{
    protected $amount;
    protected $accounts;

    public function __construct(array $accounts, int $amount)
    {
        $this->amount = $amount;
        $this->accounts = [
            'credit' => $accounts['credit'],
            'debit' => $accounts['debit']
        ];
    }

    public function checkBalance(Account $accounts)
    {
        return $accounts->balance;
    }

    public function initiateTransfer()
    {
        $this->AbortIfBalanceIsInsufficient();

        foreach ($this->accounts as $type => $account) {
            $transaction = $account->createTransaction($type, $this->amount);

            $this->completeTransfer($type, $account, $transaction);

            $this->notifyAccountOwner($account, $transaction);
        }
    }

    /**
     * @param string $type
     * @param $account
     * @param $transaction
     * @return void
     */
    protected function completeTransfer(string $type, $account, $transaction): void
    {
        $account->$type($transaction);
    }

    /**
     * @param $account
     * @param $transaction
     * @return void
     */
    protected function notifyAccountOwner($account, $transaction): void
    {
        $account->user->notify(new TransactionAlert($transaction));
    }

    /**
     * @return void
     */
    protected function AbortIfBalanceIsInsufficient(): void
    {
        abort_if($this->amount <= $this->checkBalance($this->accounts['debit']), 403, 'Insufficient Funds!');
    }
}
