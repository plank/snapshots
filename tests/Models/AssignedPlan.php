<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Plank\Snapshots\Concerns\AsSnapshottedPivot;
use Plank\Snapshots\Contracts\SnapshottedPivot;

class AssignedPlan extends MorphPivot implements SnapshottedPivot
{
    use AsSnapshottedPivot;

    protected $table = 'plannables';
}
