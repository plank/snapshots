<?php

namespace Plank\Snapshots\Connection;

use Illuminate\Database\Connection;
use Illuminate\Database\DatabaseManager;
use Illuminate\Database\MariaDbConnection;
use Illuminate\Database\MySqlConnection;
use Illuminate\Database\PostgresConnection;
use Illuminate\Database\SQLiteConnection;
use Illuminate\Database\SqlServerConnection;
use Illuminate\Foundation\Application;
use Plank\Snapshots\Facades\Versions;
use Plank\Snapshots\Migrator\Blueprint\SnapshotBlueprint;

class SnapshotConnectionInitializer
{
    public static function initialize(
        Application $app,
        DatabaseManager $db,
        string $name,
    ) {
        $connection = $db->connection($name);
        $configuredPrefix = $connection->getTablePrefix();

        $prefix = ($active = Versions::active())
            ? $active->key()->prefix($configuredPrefix)
            : $configuredPrefix;

        $config = $connection->getConfig();
        $config['prefix_indexes'] = true;
        $config['prefix'] = $prefix;

        /** @var class-string<Connection> $class */
        $class = $connection::class;

        $prefixed = new $class(
            $connection->getRawPdo(),
            $connection->getDatabaseName(),
            $prefix,
            $config,
        );

        if ($prefixed instanceof MySqlConnection) {
            $grammar = (new SnapshotMySqlGrammar($prefixed));
            $grammar->setTablePrefix($prefix);
            $prefixed->setSchemaGrammar($grammar);
        } elseif ($prefixed instanceof SQLiteConnection) {
            $grammar = (new SnapshotSQLiteGrammar($prefixed));
            $grammar->setTablePrefix($prefix);
            $prefixed->setSchemaGrammar($grammar);
        } elseif ($prefixed instanceof PostgresConnection) {
            $grammar = (new SnapshotPostgresGrammar($prefixed));
            $grammar->setTablePrefix($prefix);
            $prefixed->setSchemaGrammar($grammar);
        } elseif ($prefixed instanceof MariaDbConnection) {
            $grammar = (new SnapshotMariaDbGrammar($prefixed));
            $grammar->setTablePrefix($prefix);
            $prefixed->setSchemaGrammar($grammar);
        } elseif ($prefixed instanceof SqlServerConnection) {
            $grammar = (new SnapshotSqlServerGrammar($prefixed));
            $grammar->setTablePrefix($prefix);
            $prefixed->setSchemaGrammar($grammar);
        } else {
            $grammar = (new SnapshotGrammar($prefixed));
            $grammar->setTablePrefix($prefix);
            $prefixed->setSchemaGrammar($grammar);
        }

        $prefixed->reconnectIfMissingConnection();

        $builder = $prefixed->getSchemaBuilder();

        $builder->blueprintResolver(function ($table, $callback, $prefix) {
            return new SnapshotBlueprint($table, $callback, $prefix);
        });

        $app->instance('db.schema', $builder);

        return $prefixed;
    }
}
