<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Plank\Snapshots\Concerns\AsVersionedContent;
use Plank\Snapshots\Concerns\HasHistory;
use Plank\Snapshots\Contracts\Trackable;
use Plank\Snapshots\Contracts\Versioned;

class Document extends Model implements Trackable, Versioned
{
    use AsVersionedContent;
    use HasFactory;
    use HasHistory;

    protected $guarded = [];
}
