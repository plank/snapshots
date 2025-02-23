<?php

namespace Plank\Snapshots\Exceptions;

use Plank\Snapshots\Contracts\Version;

class MigrationFailedException extends SnapshotsException
{
    public static function create(Version $version): self
    {
        return new self("Migrations failed for Version {$version->key()}");
    }
}
