<?php

use Illuminate\Database\Schema\Builder as SchemaBuilder;
use Illuminate\Support\Facades\Schema;
use Plank\Snapshots\Migrator\Blueprint\SnapshotBlueprint;

use function Pest\Laravel\artisan;

describe('The snapshot schema works with unversioned foreign keys correctly', function () {
    beforeEach(function () {
        artisan('migrate', [
            '--path' => migrationPath('schema/unversioned_fks'),
            '--realpath' => true,
        ])->run();
    });

    it('creates the unversioned foreign keys correctly', function () {
        versions()->setActive(createFirstVersion('schema/unversioned_fks'));

        $assertFk = function (array $indexes, string $expected) {
            $columns = collect($indexes)->pluck('columns')->flatten();
            expect($columns)->toContain($expected);
        };

        usingSnapshotSchema(function (SchemaBuilder $schema) use ($assertFk) {
            expect($schema->getConnection()->getTablePrefix())->toBe('v1_0_0_');
            $assertFk($schema->getForeignKeys('versioneds'), 'unversioned_id');
            $assertFk($schema->getForeignKeys('versioned_alsos'), 'unversioned_also_id');
            $assertFk($schema->getForeignKeys('versioned_ulids'), 'unversioned_ulid_id');
            $assertFk($schema->getForeignKeys('versioned_ulid_alsos'), 'unversioned_ulid_also_id');
            $assertFk($schema->getForeignKeys('versioned_uuids'), 'unversioned_uuid_id');
            $assertFk($schema->getForeignKeys('versioned_uuid_alsos'), 'unversioned_uuid_also_id');
        });

        versions()->clearActive();
        usingSnapshotSchema(function (SchemaBuilder $schema) use ($assertFk) {
            expect($schema->getConnection()->getTablePrefix())->toBe('');
            $assertFk($schema->getForeignKeys('versioneds'), 'unversioned_id');
            $assertFk($schema->getForeignKeys('versioned_alsos'), 'unversioned_also_id');
            $assertFk($schema->getForeignKeys('versioned_ulids'), 'unversioned_ulid_id');
            $assertFk($schema->getForeignKeys('versioned_ulid_alsos'), 'unversioned_ulid_also_id');
            $assertFk($schema->getForeignKeys('versioned_uuids'), 'unversioned_uuid_id');
            $assertFk($schema->getForeignKeys('versioned_uuid_alsos'), 'unversioned_uuid_also_id');
        });
    });

    it('drops unversioned foreign keys and columns on versioned tables', function () {
        versions()->setActive(createFirstVersion('schema/unversioned_fks'));

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            $fkColumns = collect($schema->getForeignKeys('versioneds'))->pluck('columns')->flatten();
            expect($fkColumns)->toContain('unversioned_id');
        });

        usingSnapshotSchema(function () {
            Schema::table('versioneds', function (SnapshotBlueprint $table) {
                $table->dropUnversionedForeign(['unversioned_id']);
                $table->dropColumn('unversioned_id');
            });
        });

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            expect($schema->hasColumn('versioneds', 'unversioned_id'))->toBeFalse();
        });
    });

    it('drops unversioned foreign keys and columns on unversioned tables', function () {
        usingSnapshotSchema(function (SchemaBuilder $schema) {
            $fkColumns = collect($schema->getForeignKeys('versioneds'))->pluck('columns')->flatten();
            expect($fkColumns)->toContain('unversioned_id');
        });

        usingSnapshotSchema(function () {
            Schema::table('versioneds', function (SnapshotBlueprint $table) {
                $table->dropUnversionedForeign(['unversioned_id']);
                $table->dropColumn('unversioned_id');
            });
        });

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            expect($schema->hasColumn('versioneds', 'unversioned_id'))->toBeFalse();
        });
    });

    it('drops unversioned foreign keys without dropping the column', function () {
        versions()->setActive(createFirstVersion('schema/unversioned_fks'));

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            $fkColumns = collect($schema->getForeignKeys('versioneds'))->pluck('columns')->flatten();
            expect($fkColumns)->toContain('unversioned_id');
        });

        usingSnapshotSchema(function () {
            Schema::table('versioneds', function (SnapshotBlueprint $table) {
                $table->dropUnversionedForeign(['unversioned_id']);
            });
        });

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            expect($schema->hasColumn('versioneds', 'unversioned_id'))->toBeTrue();
            $fkColumns = collect($schema->getForeignKeys('versioneds'))->pluck('columns')->flatten();
            expect($fkColumns)->not->toContain('unversioned_id');
        });
    })->skip(fn () => config('database.default') === 'testing', 'SQLite does not support dropping foreign keys without dropping the column');

    it('drops constrained unversioned foreign id on versioned tables', function () {
        versions()->setActive(createFirstVersion('schema/unversioned_fks'));

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            $fkColumns = collect($schema->getForeignKeys('versioneds'))->pluck('columns')->flatten();
            expect($fkColumns)->toContain('unversioned_id');
            expect($schema->hasColumn('versioneds', 'unversioned_id'))->toBeTrue();
        });

        usingSnapshotSchema(function () {
            Schema::table('versioneds', function (SnapshotBlueprint $table) {
                $table->dropConstrainedUnversionedForeignId('unversioned_id');
            });
        });

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            expect($schema->hasColumn('versioneds', 'unversioned_id'))->toBeFalse();
        });
    });

    it('preserves other foreign keys when dropping an unversioned foreign key', function () {
        versions()->setActive(createFirstVersion('schema/unversioned_fks'));

        usingSnapshotSchema(function () {
            Schema::table('versioneds', function (SnapshotBlueprint $table) {
                $table->dropUnversionedForeign(['unversioned_id']);
                $table->dropColumn('unversioned_id');
            });
        });

        usingSnapshotSchema(function (SchemaBuilder $schema) {
            expect($schema->hasColumn('versioneds', 'unversioned_id'))->toBeFalse();
            expect($schema->hasTable('versioneds'))->toBeTrue();
        });
    });
});
