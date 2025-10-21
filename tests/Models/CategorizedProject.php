<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Plank\Snapshots\Concerns\AsVersionedPivot;
use Plank\Snapshots\Contracts\VersionedPivot;

class CategorizedProject extends Pivot implements VersionedPivot
{
    use AsVersionedPivot;

    protected $table = 'category_project';
}
