<?php

namespace Plank\Snapshots\Listeners;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Plank\LaravelSchemaEvents\Events\TableCreated;
use Plank\Snapshots\Events\TableCopied;

class TableCopier
{
    public function handle(TableCreated $created)
    {
        Schema::disableForeignKeyConstraints();

        /** @var class-string<VersionKey> $keyClass */
        $keyClass = config()->get('snapshots.value_objects.version_key');

        $from = $keyClass::strip($created->table);
        $to = $created->table;

        if ($from === $to) {
            return;
        }

        Schema::withoutForeignKeyConstraints(function () use ($from, $to) {
            DB::statement("INSERT INTO `$to` SELECT * FROM `$from`");
        });

        Event::dispatch(new TableCopied($to));

        Schema::enableForeignKeyConstraints();
    }
}
