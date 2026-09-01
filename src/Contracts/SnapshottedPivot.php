<?php

namespace Plank\Snapshots\Contracts;

interface SnapshottedPivot
{
    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable();
}
