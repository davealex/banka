<?php

namespace App\Services;

use App\Models\Account;
use App\Notifications\TransactionAlert;

class BankingService
{
    const SECURITY_CHECKS = [
        'notSameAccounts' => 'You cannot transfer from an account to itself. Please choose a different account!',
        'isSameCurrencyTransaction' => 'You can only initiate transfers between accounts of the same Currency!',
        'debitAccountBalanceIsSufficient' => 'Insufficient Funds!',
    ];

    /**
     * @var mixed
     */
    protected $amount;
    /**
     * @var array
     */
    protected $accounts;

    /**
     * @param array $accounts
     */
    public function __construct(array $accounts)
    {
        $this->amount = $accounts['amount'];
        $this->accounts = [
            'credit' => Account::whereNumber($accounts['credit']),
            'debit' => Account::whereNumber($accounts['debit']),
        ];
    }

    /**
     * @param $account
     * @return mixed
     */
    public function checkBalance($account)
    {
        return $account->first()->balance;
    }

    /**
     * @return void
     */
    public function initiateTransfer()
    {
        $this->doSecurityChecks();

        foreach ($this->accounts as $type => $account) {
            $transaction = $account->lockForUpdate()
                ->first()
                ->createTransaction($type, $this->amount);

            $this->completeTransfer($type, $account->lockForUpdate()->first(), $transaction);

            $this->notifyAccountOwner($account->first(), $transaction);
        }
    }

    /**
     * @return void
     */
    protected function doSecurityChecks(): void
    {
        foreach (self::SECURITY_CHECKS as $CHECKED => $MESSAGE) {
            abort_unless($this->$CHECKED(), 400, $MESSAGE);
        }
    }

    /**
     * @return bool
     */
    protected function notSameAccounts(): bool
    {
        return $this->accounts['credit'] !== $this->accounts['debit'];
    }

    /**
     * @return bool
     */
    protected function isSameCurrencyTransaction(): bool
    {
        return $this->accounts['credit']->first()->type_id === $this->accounts['debit']->first()->type_id;
    }

    /**
     * @return bool
     */
    protected function debitAccountBalanceIsSufficient(): bool
    {
        return $this->amount <= $this->checkBalance($this->accounts['debit']);
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
}
