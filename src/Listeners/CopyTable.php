<?php

namespace Plank\Snapshots\Listeners;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Events\TableCreated;

class CopyTable
{
    public function handle(TableCreated $event)
    {
        $version = $event->version;

        if ($version === null) {
            return;
        }

        $from = $event->table;
        $to = $version->addTablePrefix($from);

        Schema::withoutForeignKeyConstraints(function () use ($from, $to) {
            DB::statement("INSERT INTO `$to` SELECT * FROM `$from`");
        });
    }
}
