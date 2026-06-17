<?php

namespace Plank\Snapshots\Connection;

use Illuminate\Database\Connection;
use Illuminate\Database\MariaDbConnection;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Database\SqlServerConnection;

class SchemaGrammar
{
    public static function useSnapshots(Connection $connection): void
    {
        if ($connection instanceof MySqlConnection) {
            $connection->setSchemaGrammar(new SnapshotMySqlGrammar($connection));
        } elseif ($connection instanceof SQLiteConnection) {
            $connection->setSchemaGrammar(new SnapshotSQLiteGrammar($connection));
        } elseif ($connection instanceof PostgresConnection) {
            $connection->setSchemaGrammar(new SnapshotPostgresGrammar($connection));
        } elseif ($connection instanceof MariaDbConnection) {
            $connection->setSchemaGrammar(new SnapshotMariaDbGrammar($connection));
        } elseif ($connection instanceof SqlServerConnection) {
            $connection->setSchemaGrammar(new SnapshotSqlServerGrammar($connection));
        } else {
            $connection->setSchemaGrammar(new SnapshotGrammar($connection));
        }
    }
}
