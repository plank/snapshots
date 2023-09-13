<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Plank\Snapshots\Concerns\AsVersionedPivot;

class AssignedPlan extends MorphPivot
{
    use AsVersionedPivot;

    protected $table = 'plannables';
}
