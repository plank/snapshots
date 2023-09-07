<?php

use Doctrine\DBAL\Schema\SchemaException;
use Illuminate\Support\Facades\Artisan;
use Plank\Snapshots\Contracts\ManagesVersions;
use Plank\Snapshots\Migrator\SnapshotMigrator;
use Plank\Snapshots\Models\Version as VersionModel;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;

describe('SnapshotMigrations are can be pretended', function () {
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
        ])->run();
    });

    it('pretends migrations per version', function () {
        createFirstVersion('schema/create');

        assertDatabaseHas('migrations', [
            'migration' => 'v1_0_0_create_documents_table',
            'batch' => 3,
        ]);

        VersionModel::factory()->createQuietly([
            'number' => '1.0.1',
        ]);

        test()->withoutMockingConsoleOutput();

        artisan('migrate', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
            '--pretend' => true,
        ]);

        expect(Artisan::output())->toMatchSnapshot();
    });

    it('reports errors that occur during pretended migrations', function () {
        $version = createFirstVersion('schema/create');

        $migrator = new class($this->app['migration.repository'], $this->app['db'], $this->app['files'], $this->app['events'], $this->app[ManagesVersions::class]) extends SnapshotMigrator
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
