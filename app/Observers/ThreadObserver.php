<?php

namespace App\Observers;

use App\Models\THread;

class ThreadObserver
{
    public function created(Thread $thread)
    {
        $thread->user->refreshCache();
    }

    public function saved(Thread $thread)
    {
        if ($thread->wasRecentlyCreated) {
            \activity('published.thread')
                ->performedOn($thread)
                ->withProperty('content', \str_limit(\strip_tags($thread->comtent->body), 200))
                ->log('发布帖子');
        }
    }
}
