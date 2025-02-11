<?php

namespace Plank\Snapshots\Factory;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Grammars\MySqlGrammar;
use Illuminate\Database\Schema\Grammars\PostgresGrammar;
use Illuminate\Database\Schema\Grammars\SQLiteGrammar;
use Illuminate\Database\Schema\Grammars\SqlServerGrammar;
use Illuminate\Support\Facades\DB;
use Plank\Snapshots\Connection\MySqlSnapshotConnection;
use Plank\Snapshots\Connection\PostgresSnapshotConnection;
use Plank\Snapshots\Connection\SnapshotConnection;
use Plank\Snapshots\Connection\SQLiteSnapshotConnection;
use Plank\Snapshots\Connection\SqlServerSnapshotConnection;
use Plank\Snapshots\Contracts\ManagesCreatedTables;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\VersionedConnection;

class SnapshotConnectionBuilder
{
    public function __construct(
        public ManagesVersions $versions,
        public ManagesCreatedTables $tables,
    ) {}

    public function default(): VersionedConnection
    {
        return $this->from(DB::connection());
    }

    public function from(Connection $connection): VersionedConnection
    {
        if ($connection instanceof VersionedConnection) {
            return $connection;
        }

        $grammar = $connection->getSchemaGrammar();

        if (is_null($grammar)) {
            $connection->useDefaultSchemaGrammar();
            $grammar = $connection->getSchemaGrammar();
        }

        return match ($grammar ? get_class($grammar) : null) {
            MySqlGrammar::class => new MySqlSnapshotConnection(
                $connection->getRawPdo(),
                $connection->getDatabaseName(),
                $connection->getTablePrefix(),
                $connection->getConfig(),
                $this->versions,
                $this->tables,
            ),
            PostgresGrammar::class => new PostgresSnapshotConnection(
                $connection->getRawPdo(),
                $connection->getDatabaseName(),
                $connection->getTablePrefix(),
                $connection->getConfig(),
                $this->versions,
                $this->tables,
            ),
            SQLiteGrammar::class => new SQLiteSnapshotConnection(
                $connection->getRawPdo(),
                $connection->getDatabaseName(),
                $connection->getTablePrefix(),
                $connection->getConfig(),
                $this->versions,
                $this->tables,
            ),
            SqlServerGrammar::class => new SqlServerSnapshotConnection(
                $connection->getRawPdo(),
                $connection->getDatabaseName(),
                $connection->getTablePrefix(),
                $connection->getConfig(),
                $this->versions,
                $this->tables,
            ),
            default => new SnapshotConnection(
                $connection->getRawPdo(),
                $connection->getDatabaseName(),
                $connection->getTablePrefix(),
                $connection->getConfig(),
                $this->versions,
                $this->tables,
            ),
        };
    }
}
