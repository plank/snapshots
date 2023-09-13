<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Plank\Snapshots\Concerns\AsVersionedPivot;

class CategorizedProject extends Pivot
{
    use AsVersionedPivot;

    protected $table = 'category_project';
}
