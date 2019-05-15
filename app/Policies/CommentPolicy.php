<?php

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;

class CommentPolicy extends Policy
{
    public function view(User $authUser, Comment $comment)
    {
        return true;
    }

    public function create(User $authUser)
    {
        return true;
    }

    public function update(User $authUser, Comment $comment)
    {
        return $comment->user_id === $authUser->id || $authUser->can('update-comment');
    }

    public function delete(User $authUser, Comment $comment)
    {
        return $comment->user_id === $authUser->id || $authUser->can('delete-comment');
    }
}
