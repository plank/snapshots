<?php

namespace Plank\Snapshots\Concerns;

use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;
use Plank\Snapshots\Facades\Snapshots;

/**
 * @mixin \Illuminate\Database\Eloquent\Relations\Pivot
 */
trait AsSnapshottedPivot
{
    use AsPivot {
        AsPivot::getTable as getPivotTable;
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        if ($snapshot = Snapshots::active()) {
            return $snapshot->key()->prefix($this->getPivotTable());
        }

        return $this->getPivotTable();
    }
}
