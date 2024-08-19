<?php

namespace Plank\Snapshots\Contracts;

interface Versioned
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
}
