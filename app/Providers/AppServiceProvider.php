<?php

namespace App\Providers;

use App\Models\Comment;
use App\Models\Thread;
use App\Models\User;
use App\Observers\CommentObserver;
use App\Observers\ThreadObserver;
use App\Observers\UserObserver;
use App\Validators\HashValidator;
use App\Validators\KeepWordValidator;
use App\Validators\PhoneValidator;
use App\Validators\PhoneVerifyCodeValidator;
use App\Validators\PolyExistsValidator;
use App\Validators\TicketValidator;
use App\Validators\UsernameValidator;
use App\Validators\UserUniqueContentValidator;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;
use Laravel\Horizon\Horizon;

class AppServiceProvider extends ServiceProvider
{
    // 自定义验证规则扩展
    protected $validators =[
        'hash' => HashValidator::class,
        'keep_word' => KeepWordValidator::class,
        'poly_exists' => PolyExistsValidator::class,
        'phone' => PhoneValidator::class,
        'verify_code' => PhoneVerifyCodeValidator::class,
        'ticket' => TicketValidator::class,
        'username' => UsernameValidator::class,
        'user_unique_content' => UserUniqueContentValidator::class,
    ];

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Resource::withoutWrapping();

        Carbon::setLocale('zh');

        User::observe(UserObserver::class);
        Comment::observe(CommentObserver::class);
        Thread::observe(ThreadObserver::class);

        // 注册自定义的验证扩展
        $this->registerValidators();

        Horizon::auth(function ($request) {
            // Horizon 仪表盘的路由是 /horizon ，默认只能在 local 环境中访问仪表盘
            return true;
        });
    }

    /**
     * 注册验证扩展
     */
    protected function registerValidators()
    {
        foreach ($this->validators as $rule => $validator) {
            Validator::extend($rule, "{$validator}@validate");
        }
    }
}
