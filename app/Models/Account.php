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
        'manager_id',
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
        'type'
    ];

    /**
     * @param $attributes
     * @return false|int|string
     */
    public function getStatusAttribute($value): string
    {
        return array_search($value, self::STATUS);
    }

    /**
     * @return BelongsTo
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(Type::class);
    }

    /**
     * @return HasMany
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Associate related model to a User
     *
     * @return BelongsTo
     */
    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    /**
     * @param Transaction $transaction
     * @return Account|null
     */
    public function credit(Transaction $transaction): Model
    {
        $this->update([
            'balance' => $this->balance += $transaction->amount
        ]);

        return $this->fresh();
    }

    /**
     * @param Transaction $transaction
     * @return Account|null
     */
    public function debit(Transaction $transaction): Model
    {
        $this->update([
            'balance' => $this->balance -= $transaction->amount
        ]);

        return $this->fresh();
    }

    /**
     * @param string $type
     * @param int $amount
     * @return Model
     */
    public function createTransaction(string $type, int $amount): Model
    {
        return $this->transactions()->create([
            'type' => Transaction::TYPES[$type],
            'amount' => $amount,
            'user_id' => auth()->id()
        ]);
    }
}
