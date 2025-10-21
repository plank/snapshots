<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Plank\Snapshots\Concerns\AsVersionedPivot;
use Plank\Snapshots\Contracts\VersionedPivot;

class Contract extends MorphPivot implements VersionedPivot
{
    use AsVersionedPivot;

    protected $table = 'contractables';
}
