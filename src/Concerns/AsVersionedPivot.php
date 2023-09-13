<?php

namespace Plank\Snapshots\Concerns;

use Illuminate\Database\Eloquent\Relations\Concerns\AsPivot;
use Plank\Snapshots\Contracts\ManagesVersions;

/**
 * @mixin \Illuminate\Database\Eloquent\Relations\Pivot
 */
trait AsVersionedPivot
{
    use AsPivot {
        AsPivot::getTable as getPivotTable;
    }

    protected ManagesVersions $versions;

    public function initializeAsVersionedPivot()
    {
        $this->versions = $this->getVersionRepository();
    }

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable()
    {
        if ($version = $this->versions->active()) {
            return $version->addTablePrefix($this->getPivotTable());
        }

        return $this->getPivotTable();
    }

    /**
     * Resolve the version repository instance.
     */
    public function getVersionRepository(): ManagesVersions
    {
        return app(ManagesVersions::class);
    }
}
