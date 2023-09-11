<?php

namespace Plank\Snapshots\Observers;

use Illuminate\Support\Facades\Event;
use Plank\Snapshots\Events\ReleasingVersion;
use Plank\Snapshots\Events\UnreleasingVersion;
use Plank\Snapshots\Events\VersionCreated;
use Plank\Snapshots\Events\VersionReleased;
use Plank\Snapshots\Events\VersionUnreleased;
use Plank\Snapshots\Exceptions\AlreadyReleasedException;
use Plank\Snapshots\Exceptions\FutureReleaseException;
use Plank\Snapshots\Exceptions\MigrationInProgressException;
use Plank\Snapshots\Exceptions\UnreleasedVersionException;
use Plank\Snapshots\Models\Version;

class VersionObserver
{
    public function creating(Version $version)
    {
        if (($previous = $version->previous) === null) {
            return;
        }

        if (! $previous->isMigrated()) {
            throw MigrationInProgressException::create($previous);
        }

        if (! $previous->isReleased()) {
            throw UnreleasedVersionException::create($previous);
        }
    }

    public function created(Version $version)
    {
        Event::dispatch(new VersionCreated($version));
    }

    public function saving(Version $version)
    {
        if (! $version->isDirty('released_at')) {
            return;
        }

        if ($version->isReleased()) {
            if ($version->released_at !== null) {
                throw AlreadyReleasedException::create($version);
            }

            $version->beginUnrelease();
            Event::dispatch(new UnreleasingVersion($version));

            return;
        }

        // When the model is being created, released_at will be dirty and null
        if ($version->released_at === null) {
            return;
        }

        if ($version->released_at->isFuture()) {
            throw FutureReleaseException::create($version);
        }

        $version->beginRelease();
        Event::dispatch(new ReleasingVersion($version));
    }

    public function saved(Version $version)
    {
        if ($version->wasRecentlyReleased()) {
            Event::dispatch(new VersionReleased($version));
        } elseif ($version->wasRecentlyUnreleased()) {
            Event::dispatch(new VersionUnreleased($version));
        }
    }
}
