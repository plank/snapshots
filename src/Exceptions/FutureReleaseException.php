<?php

namespace Plank\Snapshots\Exceptions;

use Plank\Snapshots\Contracts\Version;

class FutureReleaseException extends SnapshotsException
{
    public static function create(Version $version): self
    {
        return new self("You cannot release Version {$version->uriKey()} in the future. The release date must be in the past.");
    }
}
