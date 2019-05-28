<?php

namespace App\Notifications;

use App\Mail\Mention;
use App\Models\Content;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class MentionedMe extends Notification implements ShouldQueue
{
    use Queueable;

    public $causer;

    public $me;

    public $content;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(User $causer, User $me, Content $content)
    {
        $this->causer = $causer;
        $this->me = $me;
        $this->content = $content;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        return (new Mention($this->causer, $notifiable, $this->content))->to($notifiable->email);
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'user_id' => $this->causer->id,
            'name' => $this->causer->name,
            'username' => $this->causer->username,
            'avatar' => $this->causer->avatar,
            'comment_id' => $this->content->contentable_id,
            'commentable_id' => $this->content->contentable->commentable_id,
            'commentable_type' => $this->content->contentable->commentable_type,
            'commentable_title' => $this->content->contentable->commentable->title,
            'content' => $this->content->activity_log_content,
        ];
    }
}
