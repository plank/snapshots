<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Plank\Snapshots\Concerns\AsSnapshottedPivot;

class PurchasedProduct extends Pivot
{
    use AsSnapshottedPivot;

    protected $table = 'product_project';
}
