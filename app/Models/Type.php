<?php

namespace App\Models;

use App\Traits\HasAccounts;
use App\Traits\UseRef;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    use HasFactory, UseRef, HasAccounts;

    /**
     * Default account types.
     *
     * @const array<string, string>
     */
    const DEFAULT_ACCOUNT_TYPES = [
        'Naira' => [
            'types' => [
                ['name' => 'Current'],
                ['name' => 'Savings']
            ],
            'currency_code' => 'NGN',
        ],
        'Dollar' => [
            'types' => [
                ['name' => 'Current'],
            ],
            'currency_code' => 'USD'
        ],
        'Pounds' => [
            'types' => [
                ['name' => 'Current'],
            ],
            'currency_code' => 'GBP'
        ]
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ref',
        'name',
        'description',
        'currency',
        'currency_code',
        'min_balance'
    ];

    public static function createDefaults()
    {
        foreach (static::DEFAULT_ACCOUNT_TYPES as $currency => $value) {
           foreach ($value['types'] as $type) {
               $name = "{$type['name']} - {$value['currency_code']}";

               if (self::whereName($name)->doesntExist()) {
                   self::create([
                       'name' => $name,
                       'currency' => $currency,
                       'currency_code' => $value['currency_code'],
                   ]);
               }
           }
        }
    }
}
