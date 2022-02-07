<?php

namespace App\Models;

use App\Traits\HasAccounts;
use App\Traits\UseRef;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, UseRef, HasAccounts;

    /**
     * Default users.
     *
     * @var array<int, string>
     */
    const DEFAULT_USERS = [
        'system' => [
            'first_name' => 'David',
            'last_name' => 'Abiola',
            'email' => 'daveabiola@gmail.com',
            'is_super_admin' => true
        ],
        'admin' => [
            'first_name' => 'Mackle',
            'last_name' => 'Ryan',
            'email' => 'mac.ryan@email.com',
            'is_super_admin' => false
        ],
        'user' => [
            'first_name' => 'Rashida',
            'last_name' => 'Jones',
            'email' => 'r_jones@email.com',
            'is_super_admin' => false
        ]
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ref',
        'first_name',
        'last_name',
        'email',
        'password',
        'is_verified'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'email_verified_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_verified' => 'boolean'
    ];

    /**
     * The attributes that should be appended to User instance.
     *
     * @var array<string, string>
     */
    protected $appends = [
        'full_name'
    ];

    /**
     * The attributes to map to this Profile instance.
     *
     * @var array<int, string>
     */
    protected $with = [
        'profile'
    ];

    /**
     * @param $user
     * @return void
     */
    public static function assignSanctumToken($user): string
    {
        if ($user->isAuthorized()) {
            return $user->createToken(request()->fingerprint(), ['auth:admin'])->plainTextToken;
        } else return $user->createToken(request()->fingerprint(), ['auth:user'])->plainTextToken;
    }

    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * @return HasOne
     */
    public function profile(): HasOne
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * @return BelongsToMany
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class)->withTimestamps();
    }

    /**
     * @return mixed
     */
    public function titles()
    {
        return $this->roles->pluck('title')->unique();
    }


    /**
     * @param $role
     * @return void
     */
    public function assignRole($role)
    {
        if (is_string($role)) {
            $role = Role::whereTitle($role)->firstOrFail();
        }

        $this->roles()->syncWithoutDetaching($role);
    }

    /**
     * @return string
     */
    protected static function randomPasswordGenerator(): string
    {
        return Str::random(8);
    }

    /**
     * @return bool
     */
    public function isAdmin(): bool
    {
        return $this->titles()->contains('admin');
    }

    /**
     * @return bool
     */
    public function isSystem(): bool
    {
        return $this->titles()->contains('system');
    }

    /**
     * @return bool
     */
    public function isAuthorized(): bool
    {
        return $this->isSystem() || $this->isAdmin();
    }

    /**
     * @return void
     */
    public static function createDefaults()
    {
        foreach (static::DEFAULT_USERS as $role => $user) {
            $userQuery = self::whereEmail($user['email']);

            if ($userQuery->doesntExist()) {
                $user = self::create(array_merge([
                    'first_name' => $user['first_name'],
                    'last_name' => $user['last_name'],
                    'email' => $user['email'],
                ], [
                    'password' => Hash::make(static::getDefaultPassword($role) ?? static::randomPasswordGenerator())
                ]));

                $user->assignRole($role);
            }
        }
    }

    /**
     * @param string $role
     * @return string
     */
    protected static function getDefaultPassword(string $role): string
    {
        $roleToUpperCase = Str::upper($role);
        $envKey = "{$roleToUpperCase}_PASSWORD";

        return env($envKey);
    }

    /**
     * @return HasMany
     */
    public function managedAccounts(): HasMany
    {
        return $this->hasMany(Account::class, 'manager_id');
    }

    /**
     * @param Type $type
     * @param User $customer
     * @param int $amount
     * @return Model
     */
    public function createCustomerAccount(Type $type, User $customer, int $amount): Model
    {
        $account = (new Account([
            'balance' => $amount,
            'status' => Account::STATUS['active']
        ]))->user()
            ->associate($customer)
            ->type()
            ->associate($type);

        $this->managedAccounts()
            ->save($account);

        return $account->refresh()->load('manager');
    }

    /**
     * @param array $request
     * @return Model
     */
    public static function createNewUser(array $request): Model
    {
        return self::firstOrCreate(
            ['email' => $request['email']],
            [
                'first_name' => $request['first_name'],
                'last_name' => $request['last_name'],
                'password' => Hash::make(Str::random(8)),
            ]
        );
    }
}
