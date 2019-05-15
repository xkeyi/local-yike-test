<?php

namespace App\Models;

use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use Filterable;

    protected $casts = [
        'data' => 'json',
    ];
}
