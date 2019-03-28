<?php

namespace App\Policies;

use App\Models\Node;
use App\Models\User;

class NodePolicy extends Policy
{
    public function view(User $authUser, Node $node)
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
    public function create(User $authUser)
    {
        return $authUser->is_admin ? true : false;
    }
}
