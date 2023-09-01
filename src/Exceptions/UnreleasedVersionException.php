<?php

namespace Plank\Snapshots\Exceptions;

use Plank\Snapshots\Contracts\Version;

class UnreleasedVersionException extends SnapshotsException
{
    public static function create(Version $version): self
    {
        return new self("You cannot create a new Version until Version {$version->uriKey()} has been released.");
    }
}
