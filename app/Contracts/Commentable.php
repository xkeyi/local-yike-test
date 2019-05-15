<?php

namespace App\Contracts;

use App\Models\Comment;

interface Commentable
{
    public function afterCommentCreated(Comment $lastComment);
}
