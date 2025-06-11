<?php

use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Support\Facades\DB;
use Plank\Snapshots\Exceptions\MigrationFailedException;
use Plank\Snapshots\Migrator\Blueprint\SnapshotBlueprint;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\partialMock;

describe('The snapshot schema prefixes tables appropriately', function () {
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
        ])->run();
    });

    it('migrates the declared table before the versions table has been migrated', function () {
        DB::table('migrations')->truncate();

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            $schema->drop('versions');
            $schema->drop('documents');
            $schema->drop('flags');
        });

        artisan('migrate', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
        ])->run();

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            expect($schema->hasTable('documents'))->toBeTrue();
        });
    });

    it('uses the declared table name when no active version is set', function () {
        versions()->clearActive();

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            $schema->create('files', function (SnapshotBlueprint $table) {
                $table->id();
                $table->string('title');
                $table->string('contents');
                $table->timestamps();
            });

            expect($schema->hasTable('files'))->toBeTrue();
        });
    });

    it('creates versioned tables', function () {
        createFirstVersion('schema/create');

        assertDatabaseHas('migrations', [
            'migration' => 'v1_0_0_create_documents_table',
            'batch' => 4,
        ]);
    });

    it('does not re-run snapshot migrations', function () {
        createFirstVersion('schema/create');

        expect(DB::table('migrations')->count())->toBe(8);

        artisan('migrate', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
        ])->run();

        expect(DB::table('migrations')->count())->toBe(8);
    });

    it('creates new tables for new versions', function () {
        createFirstVersion('schema/create');

        assertDatabaseHas('migrations', [
            'migration' => 'v1_0_0_create_documents_table',
            'batch' => 4,
        ]);

        createMinorVersion('schema/create');

        assertDatabaseHas('migrations', [
            'migration' => 'v1_1_0_create_documents_table',
            'batch' => 5,
        ]);
    });

    it('creates new tables for models for new versions', function () {
        createFirstVersion('schema/create');

        assertDatabaseHas('migrations', [
            'migration' => 'v1_0_0_create_documents_table',
            'batch' => 4,
        ]);

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            expect($schema->hasTable('documents'))->toBeTrue();
        });

        createMinorVersion('schema/create');

        assertDatabaseHas('migrations', [
            'migration' => 'v1_1_0_create_documents_table',
            'batch' => 5,
        ]);

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            expect($schema->hasTable('documents'))->toBeTrue();
        });
    });

    it('drops versioned tables', function () {
        createFirstVersion('schema/create');

        artisan('migrate', [
            '--path' => migrationPath('schema/drop'),
            '--realpath' => true,
        ])->run();

        assertDatabaseHas('migrations', [
            'migration' => 'v1_0_0_drop_documents_table',
            'batch' => 5,
        ]);
    });

    it('drops versioned tables if they exist when they exist', function () {
        createFirstVersion('schema/create');

        artisan('migrate', [
            '--path' => migrationPath('schema/drop_if_exists'),
            '--realpath' => true,
        ])->run();

        assertDatabaseHas('migrations', [
            'migration' => 'v1_0_0_drop_documents_table_if_it_exists',
            'batch' => 5,
        ]);
    });

    it('doesnt drop versioned tables when they dont exist', function () {
        createFirstVersion('schema/create');

        artisan('migrate', [
            '--path' => migrationPath('schema/drop'),
            '--realpath' => true,
        ])->run();

        artisan('migrate', [
            '--path' => migrationPath('schema/drop_if_exists'),
            '--realpath' => true,
        ])->run();

        assertDatabaseHas('migrations', [
            'migration' => 'v1_0_0_drop_documents_table_if_it_exists',
            'batch' => 6,
        ]);
    });

    it('drops versioned tables for new versions', function () {
        createFirstVersion('schema/create');

        assertDatabaseHas('migrations', [
            'migration' => 'v1_0_0_create_documents_table',
            'batch' => 4,
        ]);

        versions()->setActive(createMajorVersion('schema/create'));

        artisan('migrate', [
            '--path' => migrationPath('schema/drop'),
            '--realpath' => true,
        ])->run();

        assertDatabaseHas('migrations', [
            'migration' => 'v2_0_0_drop_documents_table',
            'batch' => 6,
        ]);
    });

    it('drops columns on the original tables', function () {
        usingSnapshotSchema(function (SchemaBuilder $schema) {
            $schema->whenTableHasColumn('documents', 'released_at', function () use ($schema) {
                expect($schema->hasColumn('documents', 'released_at'))->toBeTrue();
                expect($schema->getColumnListing('documents'))->toBe(['id', 'title', 'text', 'released_at', 'created_at', 'updated_at']);
            });
        });

        artisan('migrate', [
            '--path' => migrationPath('schema/drop_columns'),
            '--realpath' => true,
        ])->run();

        assertDatabaseHas('migrations', [
            'migration' => 'drop_columns_from_documents_table',
            'batch' => 4,
        ]);

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            $schema->whenTableDoesntHaveColumn('documents', 'released_at', function () use ($schema) {
                expect($schema->hasColumns('documents', ['released_at']))->toBeFalse();
                expect($schema->getColumnListing('documents'))->toBe(['id', 'title', 'text', 'created_at', 'updated_at']);
            });
        });
    });

    it('drops columns on the versioned tables', function () {
        versions()->setActive(createFirstVersion('schema/create'));

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            $schema->whenTableHasColumn('documents', 'released_at', function () use ($schema) {
                expect($schema->hasColumn('documents', 'released_at'))->toBeTrue();
                expect($schema->getColumnListing('documents'))->toBe(['id', 'title', 'text', 'released_at', 'created_at', 'updated_at']);
            });
        });

        artisan('migrate', [
            '--path' => migrationPath('schema/drop_columns'),
            '--realpath' => true,
        ])->run();

        assertDatabaseHas('migrations', [
            'migration' => 'drop_columns_from_documents_table',
            'batch' => 5,
        ]);

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            $schema->whenTableDoesntHaveColumn('documents', 'released_at', function () use ($schema) {
                expect($schema->hasColumns('documents', ['released_at']))->toBeFalse();
                expect($schema->getColumnListing('documents'))->toBe(['id', 'title', 'text', 'created_at', 'updated_at']);
            });
        });
    });

    it('renames the original tables', function () {
        usingSnapshotSchema(function (SchemaBuilder $schema) {
            expect($schema->hasTable('documents'))->toBeTrue();
            expect($schema->hasTable('files'))->toBeFalse();
        });

        artisan('migrate', [
            '--path' => migrationPath('schema/rename'),
            '--realpath' => true,
        ])->run();

        assertDatabaseHas('migrations', [
            'migration' => 'rename_documents_table',
            'batch' => 4,
        ]);

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            expect($schema->hasTable('documents'))->toBeFalse();
            expect($schema->hasTable('files'))->toBeTrue();
        });
    });

    it('renames the versioned tables', function () {
        usingSnapshotSchema(function (SchemaBuilder $schema) {
            expect($schema->hasTable('documents'))->toBeTrue();
            expect($schema->hasTable('files'))->toBeFalse();
        });

        versions()->setActive(createFirstVersion('schema/create'));

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            expect($schema->hasTable('documents'))->toBeTrue();
            expect($schema->hasTable('files'))->toBeFalse();
        });

        artisan('migrate', [
            '--path' => migrationPath('schema/rename'),
            '--realpath' => true,
        ])->run();

        assertDatabaseHas('migrations', [
            'migration' => 'rename_documents_table',
            'batch' => 5,
        ]);

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            expect($schema->hasTable('documents'))->toBeFalse();
            expect($schema->hasTable('files'))->toBeTrue();

            expect($schema->hasTable('documents'))->toBeFalse();
            expect($schema->hasTable('files'))->toBeTrue();
        });
    });

    it('changes columns on versioned tables', function () {
        versions()->setActive(createFirstVersion('schema/create'));

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            expect($schema->getColumnType('documents', 'title'))->toBe(varcharColumn());
            expect($schema->getColumnType('documents', 'text'))->toBe(varcharColumn());
        });

        artisan('migrate', [
            '--path' => migrationPath('schema/table'),
            '--realpath' => true,
        ])->run();

        assertDatabaseHas('migrations', [
            'migration' => 'v1_0_0_change_documents_table',
            'batch' => 5,
        ]);

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            expect($schema->getColumnType('documents', 'title'))->toBe(varcharColumn());
            expect($schema->getColumnType('documents', 'text'))->toBe('text');
        });
    });

    it('reads the indexes of versioned tables correctly', function () {
        versions()->setActive(createFirstVersion('schema/create'));

        $indexes = usingSnapshotSchema(function (SchemaBuilder $schema) {
            return $schema->getIndexes('documents');
        });

        expect($indexes)->toContain([
            'name' => 'primary',
            'columns' => [
                0 => 'id',
            ],
            'type' => null,
            'unique' => true,
            'primary' => true,
        ]);

        expect($indexes)->toContain([
            'name' => 'v1_0_0_documents_released_at_index',
            'columns' => [
                0 => 'released_at',
            ],
            'type' => null,
            'unique' => false,
            'primary' => false,
        ]);

        expect($indexes)->toContain([
            'name' => 'v1_0_0_idx_title',
            'columns' => [
                0 => 'title',
            ],
            'type' => null,
            'unique' => false,
            'primary' => false,
        ]);
    });

    it('reads the foreign keys of versioned tables correctly', function () {
        artisan('migrate', [
            '--path' => migrationPath('schema/fks'),
            '--realpath' => true,
        ])->run();

        assertDatabaseHas('migrations', [
            'migration' => 'create_signatures_table',
            'batch' => 4,
        ]);

        versions()->setActive(createFirstVersion('schema/fks'));

        $fks = usingSnapshotSchema(function (SchemaBuilder $schema) {
            return $schema->getForeignKeys('signatures');
        });

        expect($fks)->toContain([
            'name' => null,
            'columns' => [
                0 => 'document_id',
            ],
            'foreign_schema' => 'main',
            'foreign_table' => 'v1_0_0_documents',
            'foreign_columns' => [
                0 => 'id',
            ],
            'on_update' => 'no action',
            'on_delete' => 'no action',
        ]);
    });

    it('forwards non-table schema builder methods to the frameworks schema builder', function () {
        versions()->setActive(createFirstVersion('schema/create'));

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            expect($schema->hasTable('documents'))->toBeTrue();
        });

        artisan('migrate', [
            '--path' => migrationPath('schema/drop_without_fk_constraints'),
            '--realpath' => true,
        ])->run();

        assertDatabaseHas('migrations', [
            'migration' => 'v1_0_0_drop_without_fk_constraints',
            'batch' => 5,
        ]);

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            expect($schema->hasTable('documents'))->toBeFalse();
        });
    });

    it('throws an exception when the artisan command migrations fail when auto-migrating', function () {
        partialMock(\Illuminate\Contracts\Console\Kernel::class, function ($mock) {
            $mock->shouldReceive('call')->andReturn(1);
        });

        createFirstVersion('schema/create');
    })->throws(MigrationFailedException::class);
});
