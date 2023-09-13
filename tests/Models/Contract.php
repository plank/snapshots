<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Relations\MorphPivot;
use Plank\Snapshots\Concerns\AsVersionedPivot;

class Contract extends MorphPivot
{
    use AsVersionedPivot;

    protected $table = 'contractables';
}
