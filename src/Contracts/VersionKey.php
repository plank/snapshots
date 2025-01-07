<?php

namespace Plank\Snapshots\Contracts;

use Stringable;

interface VersionKey extends Stringable
{
    /**
     * Build an instance from an identifying string
     * 
     * @return static
     */
    public static function fromVersionString(string $key): static;

    /**
     * Get an identifying string representation of the version
     */
    public function key(): string;
}
