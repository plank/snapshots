<?php

namespace Plank\Snapshots\Listeners;

use Illuminate\Support\Facades\DB;
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

        if ($version->previous) {
            $from = $version->previous->addTablePrefix($from);
        }

        DB::statement("INSERT INTO `$to` SELECT * FROM `$from`");
    }
}
