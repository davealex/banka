<h1 align="center">Banka</h1>

<p align="center">
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Banka

Banka is an internal banking software API simulation that features implementation of functionalities like:

- User authentication API system.
- Bank account creation system.
- Funds management system.

## Setup guide

Here's how to set this up on your development server:
Clone this repository and cd into the root directory, then run the following commands -

```bash
// 1. installing composer dependencies

composer install
```

```bash
// 2. setting environment variables

// a. setup a mail host to see notifications in action
// b. also set default passwords for the three default users to be seeded

SYSTEM_PASSWORD=
ADMIN_PASSWORD=
USER_PASSWORD=
```

```bash
// 3. running db migration, and seeding data

php artisan migrate --seed
```

```bash
// 4. generate application API key

php artisan apikey:generate {name}

example: php artisan apikey:generate banka
```

## API documentation
You can test the APIs in Postman. See documentation via the link below:
[Banka's Postman public documentation](https://documenter.getpostman.com/view/7490481/UVeGrmHi)

## The powerhouse
Take a look at one of the most interesting cogs in the system
[BankingService](https://github.com/davealex/banka/blob/main/app/Services/BankingService.php)

```bash
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
```

## Tests
Run the test suite with the command below:
```
php artisan test
```

## License

The simulation is built on Laravel framework v8.82.0 on a PHP v7.4.26 server. This software is open-sourced and licensed under the [MIT license](https://opensource.org/licenses/MIT), so feel free to experiment with it.
