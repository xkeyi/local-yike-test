<?php

namespace App\Jobs;

use App\Models\Content;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class FetchContentMentions implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $content;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Content $content)
    {
        $this->content = $content;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        \preg_match_all('/@(?<username>\w+)/', $this->content->body, $matches);

        if (!empty($matches['username'])) {
            $mentioned = $this->content->mentions->pluck('username')->toArray();

            $newMentionedUsers = User::whereIn('username', $matches['username'])->get();

            $this->content->mentions()->saveMany($newMentionedUsers);
            $causer = $this->content->contentable->user;

            foreach ($newMentionedUsers as $user) {
                if ($causer->id !== $user->id && !\in_array($user->username, $mentioned)) {
                    \activity('mention.user')
                        ->causedBy($causer)
                        ->on($this->content->contentable)
                        ->withProperties(['content' => $this->content->activity_log_content])
                        ->log('提到了你');

                    $user->notify(new MentionedMe($causer, $user, $this->content));
                }
            }
        }
    }
}
