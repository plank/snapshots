<?php

namespace Plank\Snapshots\Contracts;

interface VersionedPivot
{
    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable();
}
