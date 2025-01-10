<?php

use Doctrine\DBAL\Schema\Exception\TableDoesNotExist;
use Plank\Snapshots\Contracts\ManagesCreatedTables;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\VersionedSchema;
use Plank\Snapshots\Migrator\SnapshotMigrator;
use Plank\Snapshots\Models\Version as VersionModel;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;

describe('SnapshotMigrations can be pretended', function () {
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
        ])->run();
    });

    it('pretends snapshot up migrations', function () {
        createFirstVersion('schema/create');

        assertDatabaseHas('migrations', [
            'migration' => 'v1_0_0_create_documents_table',
            'batch' => 4,
        ]);

        VersionModel::factory()->createQuietly([
            'number' => '1.0.1',
        ]);

        artisan('migrate', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
            '--pretend' => true,
        ])
            ->expectsOutputToContain('create table "v1_0_1_documents"')
            ->assertExitCode(0);
    });

    it('pretends framework up migrations', function () {
        artisan('migrate', [
            '--path' => migrationPath('framework'),
            '--realpath' => true,
            '--pretend' => true,
        ])
            ->expectsOutputToContain('create table "files"')
            ->assertExitCode(0);
    });

    it('pretends snapshot down migrations', function () {
        createFirstVersion('schema/create');

        assertDatabaseHas('migrations', [
            'migration' => 'v1_0_0_create_documents_table',
            'batch' => 4,
        ]);

        artisan('migrate:rollback', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
            '--pretend' => true,
        ])
            ->expectsOutputToContain('drop table "v1_0_0_documents"')
            ->expectsOutputToContain('drop table "documents"')
            ->assertExitCode(0);
    });

    it('pretends framework down migrations', function () {
        artisan('migrate', [
            '--path' => migrationPath('framework'),
            '--realpath' => true,
        ]);

        artisan('migrate:rollback', [
            '--path' => migrationPath('framework'),
            '--realpath' => true,
            '--pretend' => true,
        ])
            ->expectsOutputToContain('drop table "files"')
            ->assertExitCode(0);
    });

    it('reports errors that occur during pretended migrations', function () {
        $version = createFirstVersion('schema/create');

        $migrator = new class($this->app[VersionedSchema::class], $this->app['migration.repository'], $this->app['db'], $this->app['files'], $this->app['events'], $this->app[ManagesVersions::class], $this->app[ManagesCreatedTables::class]) extends SnapshotMigrator
        {
            public string $written = '';

            protected function getQueries($migration, $method)
            {
                throw new TableDoesNotExist('test');
            }

            protected function write($component, ...$arguments)
            {
                $this->written .= implode("\n", $arguments);
            }
        };

        $migration = include migrationPath('schema/create').'/create_documents_table.php';

        $migrator->pretendToRunVersion($version, $migration, 'up');

        expect($migrator->written)->toContain('[v1_0_0_create_documents_table] failed to dump queries.');
    });
});
