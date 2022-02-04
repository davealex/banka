<?php

namespace App\Models;

use App\Traits\HasUser;
use App\Traits\UseRef;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;

class Account extends Model
{
    use HasFactory, UseRef, HasUser;

    /**
     * Default statuses.
     *
     * @const array<string, string>
     */
    const STATUS = [
        'inactive' => 0,
        'active' => 1,
        'suspended' => 2,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ref',
        'type_id',
        'user_id',
        'manager',
        'number',
        'balance',
        'status'
    ];

    /**
     * The attributes to map to this Account instance.
     *
     * @var array<int, string>
     */
    protected $with = [
        'user',
        'type',
        'manager'
    ];

    public function getAttributeStatus($attributes)
    {
        return array_search($attributes['status'], self::STATUS);
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Associate related model to a User
     *
     * @return HasOne
     */
    public function manager(): HasOne
    {
        return $this->hasOne(User::class);
    }

    /**
     * Scope a query to only include accounts managed by specific manager.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeManagerAccounts($query, User $user): Builder
    {
        return $query->where('manager', $user->id);
    }

    public function credit(Transaction $transaction)
    {
        $this->update([
            'balance' => $this->balance += $transaction->amount
        ]);

        return $this->fresh();
    }

    public function debit(Transaction $transaction)
    {
        $this->update([
            'balance' => $this->balance -= $transaction->amount
        ]);

        return $this->fresh();
    }

    public function createTransaction(string $type, int $amount): Model
    {
        return $this->transactions()->create([
            'type' => Transaction::TYPES[$type],
            'amount' => $amount,
        ]);
    }
}
