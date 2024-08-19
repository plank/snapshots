<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Plank\Snapshots\Concerns\HasHistory;
use Plank\Snapshots\Contracts\Trackable;

class Company extends Model implements Trackable
{
    use HasFactory;
    use HasHistory;
    use SoftDeletes;

    protected $guarded = [];

    protected $hidden = [
        'secret',
    ];
}
