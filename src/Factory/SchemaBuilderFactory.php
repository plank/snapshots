<?php

namespace Plank\Snapshots\Factory;

use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder;
use Illuminate\Database\Schema\MySqlBuilder;
use Illuminate\Database\Schema\PostgresBuilder;
use Illuminate\Database\Schema\SQLiteBuilder;
use Illuminate\Database\Schema\SqlServerBuilder;
use Plank\Snapshots\Contracts\ManagesCreatedTables;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\VersionedSchema;
use Plank\Snapshots\Schema\MySqlSnapshotBuilder;
use Plank\Snapshots\Schema\PostgresSnapshotBuilder;
use Plank\Snapshots\Schema\SnapshotBuilder;
use Plank\Snapshots\Schema\SQLiteSnapshotBuilder;
use Plank\Snapshots\Schema\SqlServerSnapshotBuilder;

class SchemaBuilderFactory
{
    public static function make(
        Connection $connection,
        ManagesVersions $versions,
        ManagesCreatedTables $tables,
    ): Builder&VersionedSchema {
        return match (get_class($connection->getSchemaBuilder())) {
            MySqlBuilder::class => new MySqlSnapshotBuilder($connection, $versions, $tables),
            PostgresBuilder::class => new PostgresSnapshotBuilder($connection, $versions, $tables),
            SQLiteBuilder::class => new SQLiteSnapshotBuilder($connection, $versions, $tables),
            SqlServerBuilder::class => new SqlServerSnapshotBuilder($connection, $versions, $tables),
            default => new SnapshotBuilder($connection, $versions, $tables),
        };
    }
}
