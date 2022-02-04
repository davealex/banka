<?php

namespace App\Traits;

use App\Models\Account;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasAccounts
{
    /**
     * Associate accounts with a model instance. eg. User
     *
     * @return HasMany
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }
}
