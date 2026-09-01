<?php

namespace Plank\Snapshots\Listeners;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use Plank\LaravelSchemaEvents\Events\TableCreated;
use Plank\LaravelSchemaEvents\Facades\SchemaEvents;
use Plank\Snapshots\Events\SnapshotCreated;
use Plank\Snapshots\Events\SnapshotMigrated;
use Plank\Snapshots\Exceptions\MigrationFailedException;

class ReleaseSnapshot
{
    public function handle(SnapshotCreated $event)
    {
        // Flush any previously recorded events
        SchemaEvents::flush();

        if (Artisan::call('migrate') !== 0) {
            throw MigrationFailedException::create($event->snapshot);
        }

        $tables = SchemaEvents::get()
            ->filter(fn ($schemaEvent) => $schemaEvent instanceof TableCreated)
            ->map(function (TableCreated $created) {
                return $created->table;
            })
            ->all();

        $snapshot = $event->snapshot;
        $snapshot->migrated = true;
        $snapshot->save();

        Event::dispatch(new SnapshotMigrated($event->snapshot, $tables, $event->user));
    }
}
