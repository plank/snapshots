<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Plank\Snapshots\Concerns\AsSnapshottedContent;
use Plank\Snapshots\Contracts\Snapshotted;

class Item extends Model implements Snapshotted
{
    use AsSnapshottedContent;
    use HasFactory;

    protected $guarded = [];
}
