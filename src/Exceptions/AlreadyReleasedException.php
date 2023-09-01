<?php

namespace Plank\Snapshots\Exceptions;

use Plank\Snapshots\Contracts\Version;

class AlreadyReleasedException extends SnapshotsException
{
    public static function create(Version $version): self
    {
        return new self("You cannot modify Version {$version->uriKey()} because it has already been released.");
    }
}
