<?php

namespace App\Models;

use App\Traits\HasUser;
use App\Traits\UseRef;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    use HasFactory, UseRef, HasUser;

    /**
     * Transaction types.
     *
     * @const array<string, string>
     */
    const TYPES = [
        'debit' => 0,
        'credit' => 1,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ref',
        'user_id',
        'account_id',
        'type',
        'remark',
        'amount',
    ];

    /**
     * Associate model to a Account
     *
     * @return BelongsTo
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the transaction's type.
     *
     * @param  string  $value
     * @return string
     */
    public function getTypeAttribute($value)
    {
        return array_search($value, self::TYPES);
    }
}
