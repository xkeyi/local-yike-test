<?php

namespace App\Jobs;

use App\Notifications\ThreadSensitiveExcessive;
use App\Services\Filter\SensitiveFilter;
use App\Models\Thread;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;

class FilterThreadSensitiveWords
{
    protected $content;

    /**
     * Create a new job instance.
     *
     * @return string $content
     */
    public function __construct(string $content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function handle()
    {
        $sensitiverFilter = \app(SensitiveFilter::class);

        $isLegal = $sensitiverFilter->isLegal($this->content);

        if ($isLegal) {
            $cacheKey = 'thread_sensitive_triger_'.Auth::id();

            if (!Cache::has($cacheKey)) {
                Cache::forever($cacheKey, 0);
            }

            if (Cache::get($cacheKey) >= Thread::THREAD_SENSITIVE_TRIGGER_LIMIT) {
                // 发送邮件
                Notification::send(User::admin()->get(), new ThreadSensitiveExcessive(User::first()));
            }

            Cache::increment($cacheKey);

            $this->content = $sensitiverFilter->replace($this->content, '***');
        }

        return $this->content;
    }
}
