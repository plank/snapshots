<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Plank\Snapshots\Concerns\AsSnapshottedPivot;

class CategorizedProject extends Pivot
{
    use AsSnapshottedPivot;

    protected $table = 'category_project';
}
