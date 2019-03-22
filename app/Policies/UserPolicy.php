<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy extends Policy
{
    public function view(User $authUser, User $user)
    {
        return true;
    }

    /**
     * Determine whether the user can update the app models user.
     *
     * @param  \App\Models\User  $authUser
     * @param  \App\Models\User  $user
     * @return mixed
     */
    public function update(User $authUser, User $user)
    {
        return $user->id === $authUser->id;
    }
}
