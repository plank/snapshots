<?php

namespace Plank\Snapshots\Listeners;

use Illuminate\Support\Facades\Bus;
use Plank\Snapshots\Events\SnapshotMigrated;
use Plank\Snapshots\Facades\Snapshots;

class CopyData
{
    public function handle(SnapshotMigrated $event)
    {
        if (config()->get('snapshots.force_snapshots') && Snapshots::working($event->snapshot) === null) {
            $snapshot = $event->snapshot;
            $snapshot->copied = true;
            $snapshot->save();

            return;
        }

        Bus::chain($event->jobs())
            ->onConnection(config()->get('snapshots.queue', 'sync'))
            ->dispatch();
    }
}
