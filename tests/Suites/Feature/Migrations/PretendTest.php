<?php

use Illuminate\Support\Facades\Artisan;
use Plank\Snapshots\Models\Snapshot as SnapshotModel;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\withoutMockingConsoleOutput;

describe('SnapshotMigrations can be pretended', function () {
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
        ])->run();
    });

    it('pretends snapshot up migrations', function () {
        createFirstSnapshot('schema/create');

        assertDatabaseHas('migrations', [
            'migration' => 'v1_0_0_create_documents_table',
            'batch' => 4,
        ]);

        SnapshotModel::factory()->createQuietly([
            'number' => '1.0.1',
        ]);

        withoutMockingConsoleOutput();

        artisan('migrate', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
            '--pretend' => true,
        ]);

        $output = Artisan::output();
        expect($output)->toContain('create table "v1_0_1_documents"');
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
        createFirstSnapshot('schema/create');

        assertDatabaseHas('migrations', [
            'migration' => 'v1_0_0_create_documents_table',
            'batch' => 4,
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
});
