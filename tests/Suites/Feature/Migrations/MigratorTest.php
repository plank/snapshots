<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Tests\Models\Item;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;
use function Pest\Laravel\withoutMockingConsoleOutput;

describe('SnapshotMigrations use versions to run up and down', function () {
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
        ])->run();

        createFirstSnapshot('schema/create');
        createMinorSnapshot('schema/create');
        createPatchSnapshot('schema/create');
        createMajorSnapshot('schema/create');
    });

    it('runs snapshot migrations when new versions are created', function () {
        assertDatabaseHas('migrations', [
            'migration' => 'v1_0_0_create_documents_table',
            'batch' => 4,
        ]);

        assertDatabaseHas('migrations', [
            'migration' => 'v1_1_0_create_documents_table',
            'batch' => 5,
        ]);

        assertDatabaseHas('migrations', [
            'migration' => 'v1_1_1_create_documents_table',
            'batch' => 6,
        ]);

        assertDatabaseHas('migrations', [
            'migration' => 'v2_0_0_create_documents_table',
            'batch' => 7,
        ]);
    });

    it('tables can be altered after they have been created', function () {
        artisan('migrate', [
            '--path' => migrationPath('schema/alter'),
            '--realpath' => true,
        ])->run();

        $items = Item::factory()->count(3)->create();

        snapshots()->setActive(createPatchSnapshot('schema/alter'));

        foreach ($items as $item) {
            expect(Item::query()->whereKey($item->id)->exists())->toBeTrue();
        }
    });

    it('rolls back all versions of a snapshot migration when it is included in a rollback', function () {
        artisan('migrate:rollback', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
        ])->run();

        assertDatabaseMissing('migrations', [
            'migration' => 'v1_0_0_create_documents_table',
            'batch' => 4,
        ]);

        assertDatabaseMissing('migrations', [
            'migration' => 'v1_1_0_create_documents_table',
            'batch' => 5,
        ]);

        assertDatabaseMissing('migrations', [
            'migration' => 'v1_1_1_create_documents_table',
            'batch' => 6,
        ]);

        assertDatabaseMissing('migrations', [
            'migration' => 'v2_0_0_create_documents_table',
            'batch' => 7,
        ]);

        expect(Schema::hasTable('documents'))->toBeFalse();
        expect(Schema::hasTable('v1_0_0_documents'))->toBeFalse();
        expect(Schema::hasTable('v1_1_0_documents'))->toBeFalse();
        expect(Schema::hasTable('v1_1_1_documents'))->toBeFalse();
        expect(Schema::hasTable('v2_0_0_documents'))->toBeFalse();
    });

    it('warns you when trying to rollback a versioned migration whose file no longer exists', function () {
        app('migrator')->clearPaths();

        withoutMockingConsoleOutput();
        artisan('migrate:rollback');
        $output = Artisan::output();

        expect($output)->toMatch('/v1_0_0_create_documents_table\s*\.+\s*Migration not found\s*\n/');
        expect($output)->toMatch('/v1_1_0_create_documents_table\s*\.+\s*Migration not found\s*\n/');
        expect($output)->toMatch('/v1_1_1_create_documents_table\s*\.+\s*Migration not found\s*\n/');
        expect($output)->toMatch('/v2_0_0_create_documents_table\s*\.+\s*Migration not found\s*\n/');
    });
});
