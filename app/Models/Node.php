<?php

namespace App\Models;

use App\Traits\WithDiffForHumanTimes;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Overtrue\LaravelFollow\Traits\CanBeSubscribed;

class Node extends Model
{
    use SoftDeletes, Filterable, WithDiffForHumanTimes, CanBeSubscribed;

    protected $fillable = [
        'node_id', 'title', 'icon', 'banner', 'description', 'settings', 'cache',
        'cache->threads_count', 'cache->subscribers_count',
    ];

    protected $casts = [
        'id' => 'int',
        'node_id' => 'int',
        'settings' => 'json',
        'cache' => 'json',
    ];

    public function children()
    {
        return $this->hasMany(self::class);
    }

    public function parent()
    {
        return $this->belongsTo(self::class);
    }

    public function threads()
    {
        return $this->hasMany(Thread::class);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('node_id');
    }

    public function scopeLeaf($query)
    {
        return $query->whereNotNull('node_id');
    }

    public function refreshCache()
    {
        $this->update([
            'cache->threads_count' => $this->threads()->count(),
            'cache->subscribers_count' => $this->subscribers()->count(),
        ]);
    }

    public function getHasSubcribedAttribute()
    {
        return $this->isSubscribedBy(auth()->user());
    }
}
