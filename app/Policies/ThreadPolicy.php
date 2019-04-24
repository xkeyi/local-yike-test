<?php

namespace App\Policies;

use App\Models\Thread;
use App\Models\User;

class ThreadPolicy extends Policy
{
    public function view(User $authUser, Thread $thread)
    {
        return true;
    }

    public function create(User $authUser)
    {
        return $authUser->canCreateThread();
    }

    public function update(User $authUser, Thread $thread)
    {
        return $thread->user_id === $authUser->id;
    }

    public function delete(User $authUser, Thread $thread)
    {
        return $thread->user_id === $authUser->id;
    }
}
