<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\withoutMockingConsoleOutput;

describe('SnapshotMigrations use versions to run `down`', function () {
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
        ])->run();

        versions()->setActive(createFirstVersion('schema/create'));
        versions()->setActive(releaseAndCreateMinorVersion('schema/create'));
        versions()->setActive(releaseAndCreatePatchVersion('schema/create'));
        versions()->setActive(releaseAndCreateMajorVersion('schema/create'));
    });

    it('runs snapshot migrations when new versions are created', function () {
        assertDatabaseHas('migrations', [
            'migration' => 'v1_0_0_create_documents_table',
            'batch' => 3,
        ]);

        assertDatabaseHas('migrations', [
            'migration' => 'v1_1_0_create_documents_table',
            'batch' => 4,
        ]);

        assertDatabaseHas('migrations', [
            'migration' => 'v1_1_1_create_documents_table',
            'batch' => 5,
        ]);

        assertDatabaseHas('migrations', [
            'migration' => 'v2_0_0_create_documents_table',
            'batch' => 6,
        ]);
    });

    it('rolls back all versions of a snapshot migration when it is included in a rollback', function () {
        artisan('migrate:rollback', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
        ])->run();

        assertDatabaseMissing('migrations', [
            'migration' => 'v1_0_0_create_documents_table',
            'batch' => 3,
        ]);

        assertDatabaseMissing('migrations', [
            'migration' => 'v1_1_0_create_documents_table',
            'batch' => 4,
        ]);

        assertDatabaseMissing('migrations', [
            'migration' => 'v1_1_1_create_documents_table',
            'batch' => 5,
        ]);

        assertDatabaseMissing('migrations', [
            'migration' => 'v2_0_0_create_documents_table',
            'batch' => 6,
        ]);

        expect(Schema::hasTable('documents'))->toBeFalse();
        expect(Schema::hasTable('v1_0_0_documents'))->toBeFalse();
        expect(Schema::hasTable('v1_1_0_documents'))->toBeFalse();
        expect(Schema::hasTable('v1_1_1_documents'))->toBeFalse();
        expect(Schema::hasTable('v2_0_0_documents'))->toBeFalse();
    });

    it('warns you when trying to rollback a versioned migration whose file no longer exists', function () {
        withoutMockingConsoleOutput();
        artisan('migrate:rollback');
        $output = Artisan::output();

        expect($output)->toMatch('/v1_0_0_create_documents_table\s*\.+\s*Migration not found\s*\n/');
        expect($output)->toMatch('/v1_1_0_create_documents_table\s*\.+\s*Migration not found\s*\n/');
        expect($output)->toMatch('/v1_1_1_create_documents_table\s*\.+\s*Migration not found\s*\n/');
        expect($output)->toMatch('/v2_0_0_create_documents_table\s*\.+\s*Migration not found\s*\n/');
    });
});
