<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Validators\UsernameValidator;
use App\Validators\KeepWordValidator;
use App\Validators\TicketValidator;

class AppServiceProvider extends ServiceProvider
{
    // 自定义验证规则扩展
    protected $validators =[
        'username' => UsernameValidator::class,
        'key_word' => KeepWordValidator::class,
        'ticket' => TicketValidator::class,
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
        $this->regisgerValidators();
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
