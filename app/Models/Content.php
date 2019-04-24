<?php

namespace App\Models;

use App\Traits\OnlyActivatedUserCanCreate;
use App\Traits\WithDiffForHumanTimes;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Mews\Purifier\Facades\Purifier;

class Content extends Model
{
    use SoftDeletes, Filterable, OnlyActivatedUserCanCreate, WithDiffForHumanTimes;

    protected $fillable = [
        'contentable_id', 'contentable_type', 'body', 'markdown',
    ];

    protected $casts = [
        'id' => 'int',
        'contentable_id' => 'int',
    ];

    public static function boot()
    {
        parent::boot();

        static::saving(function ($content) {
            if ($content->getDirty('markdown') && !empty($content->markdown)) {
                $content->body = self::toHTML($content->markdown);
            }

            // mewebstudio/Purifier  XSS 跨站脚本攻击
            $content->body = Purifier::clean($content->body);
        });

        static::saved(function ($content) {
            // \dispatch(new FetchContentMentions($content));
        });
    }

    public static function toHTML(string $markdown)
    {
        return app(\ParsedownExtra::class)->text(\emoji($markdown));
    }

    public function contentable()
    {
        return $this->morphTo();
    }

    public function mentions()
    {
        return $this->belongsToMany(User::class, 'content_mention');
    }
}
