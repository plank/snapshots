<?php

namespace Plank\Snapshots\Listeners;

use Illuminate\Support\Facades\Artisan;
use Plank\Snapshots\Events\VersionCreated;
use Plank\Snapshots\Exceptions\MigrationFailedException;

class SnapshotDatabase
{
    public function handle(VersionCreated $event)
    {
        if (Artisan::call('migrate') !== 0) {
            throw MigrationFailedException::create($event->version);
        }

        $version = $event->version;
        $version->migrated = true;
        $version->save();
    }
}
