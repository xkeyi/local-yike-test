<?php

namespace App\Providers;

use App\Validators\HashValidator;
use App\Validators\KeepWordValidator;
use App\Validators\PolyExistsValidator;
use App\Validators\TicketValidator;
use App\Validators\UsernameValidator;
use App\Validators\UserUniqueContentValidator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    // 自定义验证规则扩展
    protected $validators =[
        'hash' => HashValidator::class,
        'key_word' => KeepWordValidator::class,
        'poly_exists' => PolyExistsValidator::class,
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
        // 注册自定义的验证扩展
        $this->registerValidators();
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
