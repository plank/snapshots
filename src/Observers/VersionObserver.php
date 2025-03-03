<?php

namespace Plank\Snapshots\Observers;

use Illuminate\Support\Facades\Event;
use Plank\Snapshots\Events\VersionCreated;
use Plank\Snapshots\Exceptions\MigrationInProgressException;
use Plank\Snapshots\Facades\Versions;
use Plank\Snapshots\Models\Version;

class VersionObserver
{
    public function creating(Version $version)
    {
        $previous = $version->previous ?? Versions::latest();

        if ($previous === null) {
            return;
        }

        if (! $previous->isMigrated()) {
            throw MigrationInProgressException::create($previous);
        }

        $version->previous_version_id = $previous->id;
    }

    public function created(Version $version)
    {
        Event::dispatch(new VersionCreated($version));
    }
}
