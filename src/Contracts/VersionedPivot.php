<?php

namespace Plank\Snapshots\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface VersionedPivot
{
    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable();
}
