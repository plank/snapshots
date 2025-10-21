<?php

namespace Plank\Snapshots\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface Versioned extends Trackable
{
    /**
     * Retrieve the active version of the model.
     */
    public function activeVersion(): ?static;

    /**
     * Get the table associated with the model.
     *
     * @return string
     */
    public function getTable();

    /**
     * Get the query used to select records which should be included in a snapshot
     */
    public static function snapshotQuery(): Builder;
}
