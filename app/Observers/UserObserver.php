<?php

namespace App\Observers;

use App\Models\Upload;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     *
     * @param  User $user
     * @return void
     */
    public function created(User $user)
    {
        $user->assignRole('user');

        $profile = $user->profile()->create();

        Upload::defaultUploadUrl($profile, 'avatar');
    }
}
