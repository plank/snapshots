<?php

namespace Plank\Snapshots\Migrator\Copiers;

use Plank\Snapshots\Contracts\CopiesTables;

class MySqlTableCopier implements CopiesTables
{
    public function copy(string $from, string $to): void
    {
        $this->drop($to);
        $this->create($from, $to);
        $this->copyData($from, $to);
    }

    protected function drop(string $table): void
    {
        \Illuminate\Support\Facades\DB::statement("DROP TABLE IF EXISTS `$table`");
    }

    protected function create(string $from, string $to): void
    {
        \Illuminate\Support\Facades\DB::statement("CREATE TABLE `$to` LIKE `$from`");
    }

    protected function copyData(string $from, string $to): void
    {
        \Illuminate\Support\Facades\DB::statement("INSERT INTO `$to` SELECT * FROM `$from`");
    }
}
