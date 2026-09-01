<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Plank\Snapshots\Concerns\AsSnapshottedPivot;
use Plank\Snapshots\Contracts\SnapshottedPivot;

class PurchasedProduct extends Pivot implements SnapshottedPivot
{
    use AsSnapshottedPivot;

    protected $table = 'product_project';
}
