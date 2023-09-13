<?php

use Doctrine\DBAL\Schema\SchemaException;
use Illuminate\Support\Facades\Artisan;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Contracts\Version;
use Plank\Snapshots\Migrator\SnapshotMigrator;
use Plank\Snapshots\Models\Version as VersionModel;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\withoutMockingConsoleOutput;

describe('SnapshotMigrations are can be pretended', function () {
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
            'batch' => 3,
        ]);

        VersionModel::factory()->createQuietly([
            'number' => '1.0.1',
        ]);

        withoutMockingConsoleOutput();

        artisan('migrate', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
            '--pretend' => true,
        ]);

        expect(Artisan::output())->toContain('create table "v1_0_1_documents"');
    });

    it('pretends framework up migrations', function () {
        withoutMockingConsoleOutput();

        artisan('migrate', [
            '--path' => migrationPath('framework'),
            '--realpath' => true,
            '--pretend' => true,
        ]);

        $output = Artisan::output();
        expect($output)->toContain('create table "files"');
    });

    it('pretends snapshot down migrations', function () {
        createFirstVersion('schema/create');

        assertDatabaseHas('migrations', [
            'migration' => 'v1_0_0_create_documents_table',
            'batch' => 3,
        ]);

        withoutMockingConsoleOutput();

        artisan('migrate:rollback', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
            '--pretend' => true,
        ]);

        $output = Artisan::output();
        expect($output)->toContain('drop table "v1_0_0_documents"');
        expect($output)->toContain('drop table "documents"');
    });

    it('pretends framework down migrations', function () {
        artisan('migrate', [
            '--path' => migrationPath('framework'),
            '--realpath' => true,
        ]);

        withoutMockingConsoleOutput();

        artisan('migrate:rollback', [
            '--path' => migrationPath('framework'),
            '--realpath' => true,
            '--pretend' => true,
        ]);

        $output = Artisan::output();
        expect($output)->toContain('drop table "files"');
    });

    it('reports errors that occur during pretended migrations', function () {
        $version = createFirstVersion('schema/create');

        $migrator = new class($this->app['migration.repository'], $this->app['db'], $this->app['files'], $this->app['events'], $this->app[ManagesVersions::class], $this->app[Version::class]) extends SnapshotMigrator
        {
            public array $written = [];

            protected function getQueries($migration, $method)
            {
                throw SchemaException::tableDoesNotExist('test');
            }

            protected function write($component, ...$arguments)
            {
                $this->written[] = [
                    'component' => $component,
                    'arguments' => $arguments,
                ];
            }
        };

        $migration = include migrationPath('schema/create').'/create_documents_table.php';

        $migrator->pretendToRunVersion($version, $migration, 'up');

        expect($migrator->written)->toMatchSnapshot();
    });
});