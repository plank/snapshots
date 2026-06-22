<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Plank\Snapshots\Concerns\AsSnapshottedContent;
use Plank\Snapshots\Contracts\Trackable;
use Plank\Snapshots\Contracts\Snapshotted;

class Flag extends Model implements Trackable, Snapshotted
{
    use AsSnapshottedContent;
    use HasFactory;
    use SoftDeletes;

    protected $guarded = [];
}
