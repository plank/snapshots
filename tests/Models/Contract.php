<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Plank\Snapshots\Concerns\AsSnapshottedPivot;

class Contract extends MorphPivot
{
    use AsSnapshottedPivot;

    protected $table = 'contractables';
}
