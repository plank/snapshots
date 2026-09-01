<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Plank\Snapshots\Concerns\AsSnapshottedPivot;
use Plank\Snapshots\Contracts\SnapshottedPivot;

class Contract extends MorphPivot implements SnapshottedPivot
{
    use AsSnapshottedPivot;

    protected $table = 'contractables';
}
