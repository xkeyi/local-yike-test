<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Banner extends Model
{
    use SoftDeletes, Filterable;

    protected $fillable = [
        'name', 'description', 'banners',
    ];

    protected $casts = [
        'banners' => 'array',
    ];

    // 设置模型的主键, 即 Url 中传递的是 name 的值，而不在时 id 了。
    public function getRouteKeyName()
    {
        return 'name';
    }
}
