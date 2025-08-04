<?php

namespace Plank\Snapshots\Listeners;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Plank\LaravelSchemaEvents\Events\TableCreated;
use Plank\LaravelSchemaEvents\Facades\SchemaEvents;
use Plank\Snapshots\Events\VersionCreated;
use Plank\Snapshots\Events\VersionMigrated;
use Plank\Snapshots\Exceptions\MigrationFailedException;

class ReleaseVersion
{
    public function handle(VersionCreated $event)
    {
        // Flush any previously recorded events
        SchemaEvents::flush();

        if (Artisan::call('migrate') !== 0) {
            throw MigrationFailedException::create($event->version);
        }

        $tables = SchemaEvents::get()
            ->filter(fn ($schemaEvent) => $schemaEvent instanceof TableCreated)
            ->map(function (TableCreated $created) {
                return $created->table;
            })
            ->all();

        $version = $event->version;
        $version->migrated = true;
        $version->save();

        Event::dispatch(new VersionMigrated($event->version, $tables, $event->causer));
    }
}
