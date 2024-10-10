<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Nette\NotImplementedException;
use Plank\Snapshots\Exceptions\MigrationFailedException;
use Plank\Snapshots\Facades\SnapshotSchema;
use Plank\Snapshots\Schema\SnapshotBuilder;
use Plank\Snapshots\Tests\Models\Document;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\partialMock;

describe('SnapshotMigrations use versions to run `up`', function () {
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
        ])->run();
    });

    it('migrates the declared table before the versions table has been migrated', function () {
        DB::table('migrations')->truncate();
        Schema::drop('versions');
        Schema::drop('documents');

        artisan('migrate', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
        ])->run();

        expect(Schema::hasTable('documents'))->toBeTrue();
    });

    it('uses the declared table name when no active version is set', function () {
        versions()->clearActive();

        SnapshotSchema::create('files', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('contents');
            $table->timestamps();
        });

        expect(Schema::hasTable('files'))->toBeTrue();
    });

    it('creates versioned tables', function () {
        createFirstVersion('schema/create');

        assertDatabaseHas('migrations', [
            'migration' => 'v1_0_0_create_documents_table',
            'batch' => 4,
        ]);
    });

    it('does does not re-run evoloving migrations for versions', function () {
        createFirstVersion('schema/create');

        expect(DB::table('migrations')->count())->toBe(5);

        artisan('migrate', [
            '--path' => migrationPath('schema/create'),
            '--realpath' => true,
        ])->run();

        expect(DB::table('migrations')->count())->toBe(5);
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
        createFirstVersion('schema/create_for_model');

        assertDatabaseHas('migrations', [
            'migration' => 'v1_0_0_create_documents_table',
            'batch' => 4,
        ]);

        expect(Schema::hasTable('v1_0_0_documents'))->toBeTrue();

        createMinorVersion('schema/create_for_model');

        assertDatabaseHas('migrations', [
            'migration' => 'v1_1_0_create_documents_table',
            'batch' => 5,
        ]);

        expect(Schema::hasTable('v1_1_0_documents'))->toBeTrue();
    });

    it('throws an error when you pass an improper class string to createForModel', function () {
        SnapshotSchema::createForModel('documents', function ($table) {
            // The error will already be triggered
        });
    })->throws(InvalidArgumentException::class);

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

    it('drops versioned tables for models', function () {
        SnapshotSchema::dropForModel(Document::class);

        expect(Schema::hasTable('documents'))->toBeFalse();
    });

    it('throws an error when you pass an improper class string to dropForModel', function () {
        SnapshotSchema::dropForModel('documents');
    })->throws(InvalidArgumentException::class);

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
        SnapshotSchema::whenTableHasColumn('documents', 'released_at', function () {
            expect(SnapshotSchema::hasColumn('documents', 'released_at'))->toBeTrue();
            expect(SnapshotSchema::getColumnListing('documents'))->toBe(['id', 'title', 'text', 'released_at', 'created_at', 'updated_at']);
        });

        artisan('migrate', [
            '--path' => migrationPath('schema/drop_columns'),
            '--realpath' => true,
        ])->run();

        assertDatabaseHas('migrations', [
            'migration' => 'drop_columns_from_documents_table',
            'batch' => 4,
        ]);

        SnapshotSchema::whenTableDoesntHaveColumn('documents', 'released_at', function () {
            expect(SnapshotSchema::hasColumns('documents', ['released_at']))->toBeFalse();
            expect(SnapshotSchema::getColumnListing('documents'))->toBe(['id', 'title', 'text', 'created_at', 'updated_at']);
        });
    });

    it('drops columns on the versioned tables', function () {
        versions()->setActive(createFirstVersion('schema/create'));

        SnapshotSchema::whenTableHasColumn('documents', 'released_at', function () {
            expect(SnapshotSchema::hasColumn('documents', 'released_at'))->toBeTrue();
            expect(SnapshotSchema::getColumnListing('documents'))->toBe(['id', 'title', 'text', 'released_at', 'created_at', 'updated_at']);
        });

        artisan('migrate', [
            '--path' => migrationPath('schema/drop_columns'),
            '--realpath' => true,
        ])->run();

        assertDatabaseHas('migrations', [
            'migration' => 'drop_columns_from_documents_table',
            'batch' => 5,
        ]);

        SnapshotSchema::whenTableDoesntHaveColumn('documents', 'released_at', function () {
            expect(SnapshotSchema::hasColumns('documents', ['released_at']))->toBeFalse();
            expect(SnapshotSchema::getColumnListing('documents'))->toBe(['id', 'title', 'text', 'created_at', 'updated_at']);
        });
    });

    it('renames the original tables', function () {
        expect(SnapshotSchema::hasTable('documents'))->toBeTrue();
        expect(SnapshotSchema::hasTable('files'))->toBeFalse();

        artisan('migrate', [
            '--path' => migrationPath('schema/rename'),
            '--realpath' => true,
        ])->run();

        assertDatabaseHas('migrations', [
            'migration' => 'rename_documents_table',
            'batch' => 4,
        ]);

        expect(SnapshotSchema::hasTable('documents'))->toBeFalse();
        expect(SnapshotSchema::hasTable('files'))->toBeTrue();
    });

    it('renames the versioned tables', function () {
        versions()->setActive(createFirstVersion('schema/create'));

        expect(SnapshotSchema::hasTable('documents'))->toBeTrue();
        expect(SnapshotSchema::hasTable('files'))->toBeFalse();

        artisan('migrate', [
            '--path' => migrationPath('schema/rename'),
            '--realpath' => true,
        ])->run();

        assertDatabaseHas('migrations', [
            'migration' => 'rename_documents_table',
            'batch' => 5,
        ]);

        expect(SnapshotSchema::hasTable('documents'))->toBeFalse();
        expect(SnapshotSchema::hasTable('files'))->toBeTrue();
    });

    it('changes columns on versioned tables', function () {
        versions()->setActive(createFirstVersion('schema/create'));

        expect(SnapshotSchema::getColumnType('documents', 'title'))->toBe(varcharColumn());
        expect(SnapshotSchema::getColumnType('documents', 'text'))->toBe(varcharColumn());

        artisan('migrate', [
            '--path' => migrationPath('schema/table'),
            '--realpath' => true,
        ])->run();

        assertDatabaseHas('migrations', [
            'migration' => 'v1_0_0_change_documents_table',
            'batch' => 5,
        ]);

        expect(SnapshotSchema::getColumnType('documents', 'title'))->toBe(varcharColumn());
        expect(SnapshotSchema::getColumnType('documents', 'text'))->toBe('text');
    });

    it('reads the indexes of versioned tables correctly', function () {
        if (SnapshotSchema::getFacadeRoot() instanceof SnapshotBuilder) {
            expect(fn () => SnapshotSchema::getIndexes('error'))->toThrow(NotImplementedException::class);

            return;
        }

        versions()->setActive(createFirstVersion('schema/create'));

        $indexes = SnapshotSchema::getIndexes('documents');

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
        if (SnapshotSchema::getFacadeRoot() instanceof SnapshotBuilder) {
            expect(fn () => SnapshotSchema::getForeignKeys('error'))->toThrow(NotImplementedException::class);

            return;
        }

        artisan('migrate', [
            '--path' => migrationPath('schema/fks'),
            '--realpath' => true,
        ])->run();

        assertDatabaseHas('migrations', [
            'migration' => 'create_signatures_table',
            'batch' => 4,
        ]);

        versions()->setActive(createFirstVersion('schema/fks'));

        $fks = SnapshotSchema::getForeignKeys('signatures');

        expect($fks)->toContain([
            'name' => null,
            'columns' => [
                0 => 'document_id',
            ],
            'foreign_schema' => null,
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

        expect(SnapshotSchema::hasTable('documents'))->toBeTrue();

        artisan('migrate', [
            '--path' => migrationPath('schema/drop_without_fk_constraints'),
            '--realpath' => true,
        ])->run();

        assertDatabaseHas('migrations', [
            'migration' => 'v1_0_0_drop_without_fk_constraints',
            'batch' => 5,
        ]);

        expect(SnapshotSchema::hasTable('documents'))->toBeFalse();
    });

    it('throws an exception when the artisan command migrations fail when auto-migrating', function () {
        partialMock(\Illuminate\Contracts\Console\Kernel::class, function ($mock) {
            $mock->shouldReceive('call')->andReturn(1);
        });

        createFirstVersion('schema/create');
    })->throws(MigrationFailedException::class);
});
