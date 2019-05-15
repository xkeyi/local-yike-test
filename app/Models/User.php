<?php

namespace App\Models;

use App\Mail\Activation;
use App\Mail\MailConfirmation;
use App\Mail\ResetPassword;
use App\Traits\WithDiffForHumanTimes;
use EloquentFilter\Filterable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Laravel\Passport\HasApiTokens;
use Overtrue\LaravelFollow\Traits\CanBeFollowed;
use Overtrue\LaravelFollow\Traits\CanFavorite;
use Overtrue\LaravelFollow\Traits\CanFollow;
use Overtrue\LaravelFollow\Traits\CanLike;
use Overtrue\LaravelFollow\Traits\CanSubscribe;
use Overtrue\LaravelFollow\Traits\CanVote;
use UrlSigner;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, Filterable, CanFavorite, CanLike, CanFollow, CanVote, CanSubscribe, CanBeFollowed, WithDiffForHumanTimes;

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

    protected $appends = [
        'has_banned', 'has_activated', 'created_at_timeago', 'updated_at_timeago',
    ];

    const SENSITIVE_FIELDS = [
        'last_active_at', 'banned_at', 'email', 'realname', 'phone', 'settings',
    ];

    const UPDATE_SENSITIVE_FIELDS = [
        'last_active_at', 'banned_at',
    ];

    const CACHE_FIELDS = [
        'threads_count' => 0,
        'comments_count' => 0,
        'likes_count' => 0,
        'followings_count' => 0,
        'followers_count' => 0,
        'subscriptions_count' => 0,
    ];

    const EXTENDS_FIELDS = [
        'company' => '',
        'location' => '',
        'home_url' => '',
        'github' => '',
        'twitter' => '',
        'facebook' => '',
        'instagram' => '',
        'telegram' => '',
        'coding' => '',
        'steam' => '',
        'weibo' => '',
    ];

    const ENERGY_THREAD_CREATE = -20;

    const ENERGY_COMMENT_CREATE = -2;

    const ENERGY_THREAD_LIKED = 2;

    const ENERGY_COMMENT_UP_VOTE = 2;

    const ENERGY_COMMENT_DOWN_VOTE = -5;

    const ENERGY_COMMENT_DELETE = -10;

    public static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            $user->name = $user->name ?? $user->username;

            if (User::isUsernameExists($user->username)) {
                \abort(400, '用户名已经存在');
            }
        });

        static::saving(function ($user) {
            if (Hash::needsRehash($user->password)) {
                $user->password = \bcrypt($user->password);
            }

            if (\array_has($user->getDirty(), self::UPDATE_SENSITIVE_FIELDS) && !\request()->user()->is_admin) {
                abort('非法请求！');
            }

            foreach ($user->getDirty() as $field => $value) {
                if (\ends_with($field, '_at')) {
                    $user->$field = $value ? now() : null;
                }
            }
        });
    }

    public function profiles()
    {
        return $this->hasMany(Profile::class);
    }

    public function threads()
    {
        return $this->hasMany(Thread::class);
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class, 'causer_id')->with('subject')->latest();
    }

    public function scopeRecent($query)
    {
        return $query->latest();
    }

    // ???
    public function scopePopular($query)
    {
        return $query->latest('');
    }

    public function scopeAdmin($query)
    {
        return $query->where('is_admin', true);
    }

    public function scopeValid()
    {
        return $this->whereNotNull('activated_at')->whereNull('banned_at');
    }

    public function scopeWithoutBanned()
    {
        return $this->whereNull('banned_at');
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

    public function getHasBannedAttribute()
    {
        return (bool) $this->banned_at;
    }

    public function getHasActivatedAttribute()
    {
        return (bool) $this->activated_at;
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

    public function getCacheAttribute()
    {
        return \array_merge(self::CACHE_FIELDS, \json_decode($this->attributes['cache'] ?? '{}', true));
    }

    public function getIsValidAttribute()
    {
        return $this->has_activated && !$this->has_banned;
    }

    public function getExtendsAttribute()
    {
        return \array_merge(self::EXTENDS_FIELDS, \json_decode($this->attributes['extends'] ?? '{}', true));
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

    public function sendPasswordResetNotification($token)
    {
        return Mail::to($this->email)->queue(new ResetPassword($this->email, $token));
    }

    public function sendUpdateMail($email)
    {
        return Mail::to($email)->queue(new MailConfirmation($this, $email));
    }

    public function getUpdateMailLink($email)
    {
        $params = [
            'email' => $email,
            'user_id' => $this->id,
        ];

        return UrlSigner::sign(route('user.update-email').'?'.http_build_query($params), 60);
    }

    public function canCreateThread()
    {
        return Cache::get('thread_sensitive_trigger_'.$this->id, 0) < Thread::THREAD_SENSITIVE_TRIGGER_LIMIT && $this->energy >= 0;
    }
}
