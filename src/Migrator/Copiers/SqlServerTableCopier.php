<?php

namespace Plank\Snapshots\Migrator\Copiers;

use Illuminate\Support\Facades\DB;
use Plank\Snapshots\Contracts\CopiesTables;

class SqlServerTableCopier implements CopiesTables
{
    public function copy(string $from, string $to): void
    {
        $this->drop($to);
        $this->create($from, $to);
        $this->copyData($from, $to);
    }

    protected function drop(string $table): void
    {
        DB::statement("DROP TABLE IF EXISTS [$table]");
    }

    protected function create(string $from, string $to): void
    {
        DB::statement("SELECT * INTO [$to] FROM [$from] WHERE 0 = 1");
    }

    protected function copyData(string $from, string $to): void
    {
        DB::statement("INSERT INTO [$to] SELECT * FROM [$from]");
    }
}
