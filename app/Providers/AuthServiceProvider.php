<?php

namespace App\Providers;

use App\Models\Banner;
use App\Models\Comment;
use App\Models\Node;
use App\Models\Thread;
use App\Models\User;
use App\Policies\BannerPolicy;
use App\Policies\CommentPolicy;
use App\Policies\NodePolicy;
use App\Policies\ThreadPolicy;
use App\Policies\UserPolicy;
use Laravel\Passport\Passport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
        Node::class => NodePolicy::class,
        User::class => UserPolicy::class,
        Thread::class => ThreadPolicy::class,
        Comment::class => CommentPolicy::class,
        Banner::class => BannerPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Passport::routes();
    }
}
