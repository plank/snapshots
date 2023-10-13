<?php

namespace Plank\Snapshots\Observers;

use Illuminate\Support\Facades\Event;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Events\VersionCreated;
use Plank\Snapshots\Exceptions\MigrationInProgressException;
use Plank\Snapshots\Models\Version;

class VersionObserver
{
    public function __construct(
        protected ManagesVersions $versions
    ) {
    }

    public function creating(Version $version)
    {
        $previous = $version->previous ?? $this->versions->latest();

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
