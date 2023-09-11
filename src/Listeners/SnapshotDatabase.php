<?php

namespace Plank\Snapshots\Listeners;

use Illuminate\Support\Facades\Artisan;
use Plank\Snapshots\Events\VersionCreated;
use Plank\Snapshots\Exceptions\MigrationFailedException;

class SnapshotDatabase
{
    public function handle(VersionCreated $event)
    {
        $options = [];

        if ($path = config('snapshots.migration_path')) {
            $options = [
                '--path' => $path,
                '--realpath' => true,
            ];
        }

        if (Artisan::call('migrate', $options) !== 0) {
            throw MigrationFailedException::create($event->version);
        }

        $version = $event->version;
        $version->migrated = true;
        $version->save();
    }
}
