<?php

use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\Schema\MySqlBuilder;
use Illuminate\Database\Schema\PostgresBuilder;
use Illuminate\Database\Schema\SQLiteBuilder;
use Illuminate\Database\Schema\SqlServerBuilder;
use Plank\Snapshots\Contracts\ManagesCreatedTables;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Factory\SchemaBuilderFactory;
use Plank\Snapshots\Schema\MySqlSnapshotBuilder;
use Plank\Snapshots\Schema\PostgresSnapshotBuilder;
use Plank\Snapshots\Schema\SQLiteSnapshotBuilder;
use Plank\Snapshots\Schema\SqlServerSnapshotBuilder;

describe('The Schema Builder is resolved in the container based on the db connection', function () {
    it('resolves the MySql Snapshot Builder correctly', function () {
        $connection = (new ConnectionFactory(app()))->make(config()->get('database.connections.mysql'));

        $instance = SchemaBuilderFactory::make(
            $connection,
            app(ManagesVersions::class),
            app(ManagesCreatedTables::class)
        );

        expect($instance)->toBeInstanceOf(MySqlBuilder::class);
        expect($instance)->toBeInstanceOf(MySqlSnapshotBuilder::class);
    });

    it('resolves the Postgres Snapshot Builder correctly', function () {
        $connection = (new ConnectionFactory(app()))->make(config()->get('database.connections.pgsql'));

        $instance = SchemaBuilderFactory::make(
            $connection,
            app(ManagesVersions::class),
            app(ManagesCreatedTables::class)
        );

        expect($instance)->toBeInstanceOf(PostgresBuilder::class);
        expect($instance)->toBeInstanceOf(PostgresSnapshotBuilder::class);
    });

    it('resolves the SQLite Snapshot Builder correctly', function () {
        $connection = (new ConnectionFactory(app()))->make(config()->get('database.connections.sqlite'));

        $instance = SchemaBuilderFactory::make(
            $connection,
            app(ManagesVersions::class),
            app(ManagesCreatedTables::class)
        );

        expect($instance)->toBeInstanceOf(SQLiteBuilder::class);
        expect($instance)->toBeInstanceOf(SQLiteSnapshotBuilder::class);
    });

    it('resolves the SqlServer Snapshot Builder correctly', function () {
        $connection = (new ConnectionFactory(app()))->make(config()->get('database.connections.sqlsrv'));

        $instance = SchemaBuilderFactory::make(
            $connection,
            app(ManagesVersions::class),
            app(ManagesCreatedTables::class)
        );

        expect($instance)->toBeInstanceOf(SqlServerBuilder::class);
        expect($instance)->toBeInstanceOf(SqlServerSnapshotBuilder::class);
    });
});
