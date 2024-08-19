<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Plank\Snapshots\Concerns\AsVersionedContent;
use Plank\Snapshots\Concerns\HasHistory;
use Plank\Snapshots\Contracts\Trackable;
use Plank\Snapshots\Contracts\Versioned;

class Flag extends Model implements Trackable, Versioned
{
    use AsVersionedContent;
    use HasFactory;
    use HasHistory;
    use SoftDeletes;

    protected $guarded = [];
}
