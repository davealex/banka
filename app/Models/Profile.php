<?php

namespace App\Models;

use App\Contracts\MustImplementUpload;
use App\Traits\HasUpload;
use App\Traits\HasUser;
use App\Traits\UseRef;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model implements MustImplementUpload
{
    use HasFactory, UseRef, HasUser, HasUpload;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'ref',
        'gender',
        'username',
        'dob',
        'phone_number',
        'country',
        'state',
        'city',
        'occupation'
    ];

    /**
     * The attributes to map to this Profile instance.
     *
     * @var array<int, string>
     */
    protected $with = [
        'upload'
    ];
}
