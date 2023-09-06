<?php

namespace Plank\Snapshots\Factories;

use Plank\Snapshots\Contracts\CopiesTables;
use Plank\Snapshots\Migrator\Copiers\MySqlTableCopier;
use Plank\Snapshots\Migrator\Copiers\PostgresTableCopier;
use Plank\Snapshots\Migrator\Copiers\SqliteTableCopier;
use Plank\Snapshots\Migrator\Copiers\SqlServerTableCopier;

class TableCopierFactory
{
    public static function forDriver(string $driver): CopiesTables
    {
        switch ($driver) {
            case 'mysql':
                return new MySqlTableCopier();
            case 'pgsql':
                return new PostgresTableCopier();
            case 'sqlite':
                return new SqliteTableCopier();
            case 'sqlsrv':
                return new SqlServerTableCopier();
            default:
                throw new \InvalidArgumentException("Unsupported database driver [$driver]");
        }
    }
}
