<?php

namespace App\Providers;

use App\Listeners\RelationToggledListener;
use App\Models\Thread;
use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Overtrue\LaravelFollow\Events\RelationAttached;
use Overtrue\LaravelFollow\Events\RelationDetached;
use Overtrue\LaravelFollow\Events\RelationToggled;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        RelationToggled::class => [
            RelationToggledListener::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Event::Listen(RelationToggled::class, function ($event) {
            if (!empty($event->attached)) {
                foreach ($$event->attached as $threadId) {
                    Thread::find($threadId)->user->userEnergyUpdate($event->getRelationType());
                }
            }

            if (!empty($event->detached)) {
                foreach ($$event->detached as $threadId) {
                    Thread::find($threadId)->user->userEnergyUpdate($event->getRelationType().'-cancel');
                }
            }
        });

        Event::listen(RelationAttached::class, function ($event) {
            $event->getTargetsCollection()->map(function ($target) use ($event) {
                $target->user->userEnergyUpdate($event->getRelationType());
            });
        });

        Event::listen(RelationDetached::class, function ($event) {
            $event->getTargetsCollection()->map(function ($target) use ($event) {
                $target->user->userEnergyUpdate($event->getRelationType().'-cancel');
            });
        });
    }
}
