<?php

namespace Plank\Snapshots\Listeners;

use Illuminate\Support\Facades\Bus;
use Plank\Snapshots\Events\VersionMigrated;
use Plank\Snapshots\Facades\Versions;

class CopyData
{
    public function handle(VersionMigrated $event)
    {
        if (config()->get('snapshots.force_versions') && Versions::working($event->version) === null) {
            $version = $event->version;
            $version->copied = true;
            $version->save();

            return;
        }

        Bus::chain($event->jobs())
            ->onConnection(config()->get('snapshots.release.copy.queue', 'sync'))
            ->dispatch();
    }
}
