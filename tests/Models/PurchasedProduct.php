<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Plank\Snapshots\Concerns\AsVersionedPivot;

class PurchasedProduct extends Pivot
{
    use AsVersionedPivot;

    protected $table = 'product_project';
}
