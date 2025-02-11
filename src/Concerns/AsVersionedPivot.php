<?php

namespace Plank\Snapshots\Concerns;

use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;
use Plank\Snapshots\Facades\Versions;

/**
 * @mixin \Illuminate\Database\Eloquent\Relations\Pivot
 */
trait AsVersionedPivot
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
        if ($version = Versions::active()) {
            return $version->key()->prefix($this->getPivotTable());
        }

        return $this->getPivotTable();
    }
}
