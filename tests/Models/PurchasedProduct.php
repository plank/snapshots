<?php

namespace Plank\Snapshots\Tests\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Plank\Snapshots\Concerns\AsVersionedPivot;
use Plank\Snapshots\Contracts\VersionedPivot;

class PurchasedProduct extends Pivot implements VersionedPivot
{
    use AsVersionedPivot;

    protected $table = 'product_project';
}
