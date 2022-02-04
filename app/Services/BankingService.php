<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\TransactionAlert;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;

class BankingService
{
    protected $amount;
    protected $accounts;

    public function __construct(array $accounts)
    {
        $this->amount = $accounts['amount'];
        $this->accounts = [
            'credit' => Account::whereNumber($accounts['credit']),
            'debit' => Account::whereNumber($accounts['debit'])
        ];
    }

    public function checkBalance($account)
    {
        return $account->first()->balance;
    }

    public function initiateTransfer()
    {
        $this->AbortIfDetailsAreInaccurate();

        foreach ($this->accounts as $type => $account) {
            $transaction = $account->lockForUpdate()
                ->first()
                ->createTransaction($type, $this->amount);

            $this->completeTransfer($type, $account->lockForUpdate()->first(), $transaction);

            $this->notifyAccountOwner($account->first(), $transaction);
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
    protected function AbortIfDetailsAreInaccurate(): void
    {
        abort_unless($this->amount <= $this->checkBalance($this->accounts['debit']), 403, 'Insufficient Funds!');
        abort_if($this->accounts['credit'] === $this->accounts['debit'], 403, 'Insufficient Funds!');
    }
}
