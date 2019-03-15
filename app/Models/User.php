<?php

namespace App\Models;

use App\Mail\Activation;
use Laravel\Passport\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use UrlSigner;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'username', 'energy', 'email', 'password', 'activated_at', 'avatar', 'realname', 'phone',
        'bio', 'extends', 'settings', 'level', 'is_admin', 'cache', 'gender',
        'last_active_at', 'banned_at', 'activated_at',
    ];

    protected $hidden = [
        'password', 'remember_token', 'phone',
    ];

    protected $dates = [
        'last_active_at', 'activated_at',
    ];

    protected $casts = [
        'id' => 'int',
        'energy' => 'int',
        'is_admin' => 'bool',
        'extends' => 'json',
        'cache' => 'json',
        'settings' => 'json',
    ];

    public static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->name = $user->name ?? $user->username;

            if (User::isUsernameExists($user->username)) {
                \abort(400, '用户名已经存在');
            }
        });
    }

    public static function isUsernameExists(string $username)
    {
        return self::whereRaw(\sprintf('lower(username) = "%s" ', \strtolower($username)))->exists();
    }

    public function sendActiveMail()
    {
        return Mail::to($this->email)->queue(new Activation($this));
    }

    public function getActivationLink()
    {
        return UrlSigner::sign(route('user.activate').'?'.http_build_query(['email' => $this->email]), 60);
    }

    public function activate()
    {
        return $this->update(['activated_at' => now()]);
    }

    public function getAvatarAttribute()
    {
        if (empty($this->attributes['avatar'])) {
            $filename = \sprintf('avatars/%s.png', $this->id);
            $filepath = \storage_path('app/public/'.$filename);

            if (!\is_dir(\dirname($filepath))) {
                \mkdir(\dirname($filepath), 0755, true);
            }

            \Avatar::create($this->name)->save(Storage::disk('public')->path($filename));

            // asset 函数使用当前请求的协议（HTTP 或 HTTPS）为资源文件生成 URL
            $this->update(['avatar' => \asset(\sprintf('storage/%s', $filename))]);
        }

        return $this->attributes['avatar'];
    }

    /**
     * Find the user identified by the given $identifier.
     *
     * @param $identifier email|username
     *
     * @return mixed
     */
    public function findForPassport($identifier)
    {
        return self::orWhere('email', $identifier)->orWhere('username', $identifier)->first();
    }
}
