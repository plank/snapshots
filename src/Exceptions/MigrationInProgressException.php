<?php

namespace Plank\Snapshots\Exceptions;

use Plank\Snapshots\Contracts\Snapshot;

class MigrationInProgressException extends SnapshotsException
{
    public static function create(Snapshot $snapshot): self
    {
        return new self("Snapshot {$snapshot->key()} has not finished migrating. You cannot create a new Snapshot until it is complete.");
    }
}
