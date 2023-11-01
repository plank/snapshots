<?php

namespace Plank\Snapshots\Contracts;

interface ResolvesCauser
{
    /**
     * Determine the model or object responsible for current changes.
     */
    public function active(): ?CausesChanges;
}
