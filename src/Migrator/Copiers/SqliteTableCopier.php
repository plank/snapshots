<?php

namespace Plank\Snapshots\Migrator\Copiers;

use Illuminate\Support\Facades\DB;
use Plank\Snapshots\Contracts\CopiesTables;

class SqliteTableCopier implements CopiesTables
{
    public function copy(string $from, string $to): void
    {
        DB::statement("INSERT INTO `$to` SELECT * FROM `$from`");
    }
}
