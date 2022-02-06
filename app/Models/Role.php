<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use HasFactory;

    /**
     * Default roles.
     *
     * @const array<string, string>
     */
    const ROLES = [
        'system',
        'admin',
        'user'
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description'
    ];

    /**
     * A role can belong to many users
     *
     * @return BelongsToMany
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }


    /**
     * @return void
     */
    public static function createDefaults()
    {
        foreach (self::ROLES as $role) {
            $roleMatchQuery = Role::whereTitle($role);

            if ($roleMatchQuery->doesntExist()) {
                self::create([
                    'title' => $role
                ]);
            }
        }
    }
}
