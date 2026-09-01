<?php

namespace Plank\Snapshots\Observers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Plank\Snapshots\Events\SnapshotCreated;
use Plank\Snapshots\Exceptions\MigrationInProgressException;
use Plank\Snapshots\Facades\Snapshots;
use Plank\Snapshots\Models\Snapshot;

class SnapshotObserver
{
    public function creating(Snapshot $snapshot)
    {
        $previous = $snapshot->previous ?? Snapshots::latest();

        if ($previous === null) {
            return;
        }

        if (! $previous->isMigrated()) {
            throw MigrationInProgressException::create($previous);
        }

        $snapshot->previous_snapshot_id = $previous->id;
    }

    public function created(Snapshot $snapshot)
    {
        Event::dispatch(new SnapshotCreated($snapshot, Auth::user()));
    }
}
