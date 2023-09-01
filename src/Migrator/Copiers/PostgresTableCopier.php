<?php

namespace Plank\Snapshots\Migrator\Copiers;

use Illuminate\Support\Facades\DB;
use Plank\Snapshots\Contracts\CopiesTables;

class PostgresTableCopier implements CopiesTables
{
    public function copy(string $from, string $to): void
    {
        $this->copyTable($from, $to);
        $this->copyIndexes($from, $to);
        $this->copyForeignKeys($from, $to);
    }

    protected function copyTable(string $from, string $to): void
    {
        DB::statement("CREATE TABLE $to (LIKE $from INCLUDING ALL)");
    }

    protected function copyIndexes(string $from, string $to): void
    {
        $indexes = DB::select("SELECT indexname FROM pg_indexes WHERE tablename = '$from'");

        foreach ($indexes as $index) {
            DB::statement("CREATE INDEX {$index->indexname} ON $to (LIKE {$index->indexname} INCLUDING ALL)");
        }
    }

    protected function copyForeignKeys(string $from, string $to): void
    {
        $foreignKeys = DB::select("SELECT conname FROM pg_constraint WHERE conrelid = '$from'::regclass AND contype = 'f'");

        foreach ($foreignKeys as $foreignKey) {
            DB::statement("ALTER TABLE $to ADD CONSTRAINT {$foreignKey->conname} FOREIGN KEY (LIKE {$foreignKey->conname} INCLUDING ALL)");
        }
    }
}
