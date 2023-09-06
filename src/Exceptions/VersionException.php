<?php

namespace Plank\Snapshots\Exceptions;

use Plank\Snapshots\Contracts\Version;

class VersionException extends SnapshotsException
{
    /**
     * @param  class-string  $model
     */
    public static function create(string $model): self
    {
        return new self("The model specified in the snapshots config file ({$model}) must implement ".Version::class);
    }
}
