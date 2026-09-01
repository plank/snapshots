<?php

namespace Plank\Snapshots\Exceptions;

use Plank\Snapshots\Contracts\Snapshot;

class MigrationFailedException extends SnapshotsException
{
    public static function create(Snapshot $snapshot): self
    {
        return new self("Migrations failed for Snapshot {$snapshot->key()}");
    }
}
