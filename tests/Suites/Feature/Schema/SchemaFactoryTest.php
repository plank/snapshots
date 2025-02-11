<?php

use Illuminate\Database\SQLiteConnection;
use Illuminate\Support\Facades\DB;
use Plank\Snapshots\Connection\MySqlSnapshotConnection;
use Plank\Snapshots\Connection\PostgresSnapshotConnection;
use Plank\Snapshots\Connection\SnapshotConnection as BaseSnapshotConnection;
use Plank\Snapshots\Connection\SQLiteSnapshotConnection;
use Plank\Snapshots\Connection\SqlServerSnapshotConnection;
use Plank\Snapshots\Facades\SnapshotConnection;
use Plank\Snapshots\Schema\MySqlSnapshotBuilder;
use Plank\Snapshots\Schema\PostgresSnapshotBuilder;
use Plank\Snapshots\Schema\SnapshotBuilder;
use Plank\Snapshots\Schema\SQLiteSnapshotBuilder;
use Plank\Snapshots\Schema\SqlServerSnapshotBuilder;

describe('The Schema Builder is resolved in the container based on the db connection', function () {
    it('resolves the MySql Snapshot Builder correctly', function () {
        $instance = SnapshotConnection::from(DB::connection('mysql'));

        expect($instance)->toBeInstanceOf(MySqlSnapshotConnection::class);
        expect($instance->getSchemaBuilder())->toBeInstanceOf(MySqlSnapshotBuilder::class);
    });

    it('resolves the Postgres Snapshot Builder correctly', function () {
        $instance = SnapshotConnection::from(DB::connection('pgsql'));

        expect($instance)->toBeInstanceOf(PostgresSnapshotConnection::class);
        expect($instance->getSchemaBuilder())->toBeInstanceOf(PostgresSnapshotBuilder::class);
    });

    it('resolves the SQLite Snapshot Builder correctly', function () {
        $instance = SnapshotConnection::from(DB::connection('testing'));

        if ($instance instanceof SQLiteConnection) {
            expect($instance)->toBeInstanceOf(SQLiteSnapshotConnection::class);
            expect($instance->getSchemaBuilder())->toBeInstanceOf(SQLiteSnapshotBuilder::class);
        } else {
            expect($instance)->toBeInstanceOf(BaseSnapshotConnection::class);
            expect($instance->getSchemaBuilder())->toBeInstanceOf(SnapshotBuilder::class);
        }
    });

    it('resolves the SqlServer Snapshot Builder correctly', function () {
        $instance = SnapshotConnection::from(DB::connection('sqlsrv'));

        expect($instance)->toBeInstanceOf(SqlServerSnapshotConnection::class);
        expect($instance->getSchemaBuilder())->toBeInstanceOf(SqlServerSnapshotBuilder::class);
    });
});
