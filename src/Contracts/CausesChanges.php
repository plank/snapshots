<?php

namespace Plank\Snapshots\Contracts;

interface CausesChanges
{
    /**
     * Get the class name for polymorphic relations.
     *
     * @return string
     */
    public function getMorphClass();

    /**
     * Get the value of the model's primary key.
     *
     * @return string|int
     */
    public function getKey();
}
