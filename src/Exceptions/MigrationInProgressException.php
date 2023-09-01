<?php

namespace Plank\Snapshots\Exceptions;

use Plank\Snapshots\Contracts\Version;

class MigrationInProgressException extends SnapshotsException
{
    public static function create(Version $version): self
    {
        return new self("Version {$version->uriKey()} has not finished migrating. You cannot create a new Version until it is complete.");
    }
}
