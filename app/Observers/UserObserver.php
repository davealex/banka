<?php

namespace App\Observers;

use App\Contracts\MustImplementUpload;
use App\Models\Profile;
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

    /**
     * Handle the User "updated" event.
     *
     * @param  User $user
     * @return void
     */
    public function updated(User $user)
    {
        //
    }

    /**
     * Handle the User "deleted" event.
     *
     * @param  User $user
     * @return void
     */
    public function deleted(User $user)
    {
        //
    }

    /**
     * Handle the User "restored" event.
     *
     * @param User $user
     * @return void
     */
    public function restored(User $user)
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     *
     * @param  User $user
     * @return void
     */
    public function forceDeleted(User $user)
    {
        //
    }
}
