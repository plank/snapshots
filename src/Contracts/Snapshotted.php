<?php

namespace Plank\Snapshots\Contracts;

interface Snapshotted extends Trackable
{
    /**
     * Retrieve the active snapshot of the model.
     */
    public function activeSnapshot(): ?static;

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable();
}
